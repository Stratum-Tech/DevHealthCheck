<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Log\LogParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LogAnalyzeCommand extends Command
{
    private const DEFAULT_LOGS    = ['exception.log', 'system.log'];
    private const DEFAULT_LINES   = 2000;
    private const DEFAULT_SINCE   = 24;
    private const DEFAULT_TOP     = 20;
    private const DEFAULT_LEVEL   = 'ERROR';

    private const VALID_LEVELS = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __construct(
        private readonly LogParser $logParser,
        private readonly DirectoryList $directoryList,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('dev:healthcheck:logs')
            ->setDescription('Analyze Magento log files and produce a structured JSON error report')
            ->addOption(
                'log', 'l',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Log file(s) to analyze (relative to var/log/ or absolute path). '
                    . 'Defaults to exception.log and system.log. Repeatable: --log a.log --log b.log'
            )
            ->addOption(
                'output', 'o',
                InputOption::VALUE_REQUIRED,
                'Write JSON report to this file instead of stdout'
            )
            ->addOption(
                'lines', null,
                InputOption::VALUE_REQUIRED,
                'Lines to read from the end of each file (0 = entire file)',
                self::DEFAULT_LINES
            )
            ->addOption(
                'since', null,
                InputOption::VALUE_REQUIRED,
                'Only include entries from the last N hours (0 = no limit)',
                self::DEFAULT_SINCE
            )
            ->addOption(
                'level', null,
                InputOption::VALUE_REQUIRED,
                'Minimum severity to include: DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY',
                self::DEFAULT_LEVEL
            )
            ->addOption(
                'top', null,
                InputOption::VALUE_REQUIRED,
                'Number of top (most frequent) errors to include in the report',
                self::DEFAULT_TOP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logs       = $input->getOption('log') ?: self::DEFAULT_LOGS;
        $outputPath = $input->getOption('output');
        $maxLines   = (int) $input->getOption('lines');
        $sinceHours = (int) $input->getOption('since');
        $level      = strtoupper((string) $input->getOption('level'));
        $topCount   = (int) $input->getOption('top');

        if (!in_array($level, self::VALID_LEVELS, true)) {
            $output->writeln("<error>Invalid level '{$level}'. Valid values: " . implode(', ', self::VALID_LEVELS) . '</error>');
            return Command::FAILURE;
        }

        $since = $sinceHours > 0 ? new \DateTimeImmutable("-{$sinceHours} hours") : null;
        $varLog = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/log/';

        $allEntries = [];
        $fileStats  = [];

        foreach ($logs as $log) {
            $absPath = str_starts_with($log, '/') ? $log : $varLog . $log;
            $exists  = file_exists($absPath) && is_readable($absPath);
            $label   = basename($log);

            $fileStats[$label] = [
                'path'          => $absPath,
                'exists'        => $exists,
                'size_bytes'    => $exists ? (int) filesize($absPath) : 0,
                'size_human'    => $exists ? $this->humanSize((int) filesize($absPath)) : '—',
                'entries_found' => 0,
            ];

            if (!$exists) {
                continue;
            }

            $entries = $this->logParser->parse($log, $maxLines, $level, $since);
            $fileStats[$label]['entries_found'] = count($entries);
            $allEntries = array_merge($allEntries, $entries);
        }

        // Group entries by fingerprint (level + normalised message)
        $grouped = [];
        foreach ($allEntries as $entry) {
            $fp = $this->fingerprint($entry->level, $entry->message);
            if (!isset($grouped[$fp])) {
                $grouped[$fp] = [
                    'count'           => 0,
                    'level'           => $entry->level,
                    'message'         => $entry->message,
                    'exception_class' => $entry->exceptionClass,
                    'first_seen'      => $entry->timestamp,
                    'last_seen'       => $entry->timestamp,
                    'files'           => [],
                ];
            }
            $grouped[$fp]['count']++;
            if ($entry->timestamp < $grouped[$fp]['first_seen']) {
                $grouped[$fp]['first_seen'] = $entry->timestamp;
            }
            if ($entry->timestamp > $grouped[$fp]['last_seen']) {
                $grouped[$fp]['last_seen'] = $entry->timestamp;
            }
            if (!in_array($entry->file, $grouped[$fp]['files'], true)) {
                $grouped[$fp]['files'][] = $entry->file;
            }
        }

        // Sort by occurrence count descending
        usort($grouped, static fn($a, $b) => $b['count'] <=> $a['count']);

        // Build level summary
        $byLevel = [];
        foreach ($allEntries as $entry) {
            $byLevel[$entry->level] = ($byLevel[$entry->level] ?? 0) + 1;
        }
        arsort($byLevel);

        // Serialise datetime objects in top errors
        $topErrors = array_slice(array_values($grouped), 0, $topCount);
        foreach ($topErrors as &$err) {
            $err['first_seen'] = $err['first_seen']->format(\DateTimeInterface::ATOM);
            $err['last_seen']  = $err['last_seen']->format(\DateTimeInterface::ATOM);
        }
        unset($err);

        $report = [
            'generated_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'config' => [
                'files_analyzed'  => $logs,
                'lines_per_file'  => $maxLines === 0 ? 'all' : $maxLines,
                'since_hours'     => $sinceHours === 0 ? 'all' : $sinceHours,
                'min_level'       => $level,
            ],
            'files'   => $fileStats,
            'summary' => [
                'total_entries' => count($allEntries),
                'unique_errors' => count($grouped),
                'by_level'      => $byLevel,
            ],
            'top_errors' => $topErrors,
        ];

        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($outputPath) {
            if (file_put_contents($outputPath, $json) === false) {
                $output->writeln("<error>Could not write to '{$outputPath}'</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Report written to: {$outputPath}</info>");
            $output->writeln(sprintf(
                '<info>%d entries (%d unique) across %d file(s)</info>',
                count($allEntries),
                count($grouped),
                count($logs)
            ));
        } else {
            $output->write($json);
        }

        return Command::SUCCESS;
    }

    /**
     * Produce a stable fingerprint for grouping similar log messages together.
     * Numbers, hex IDs, and long hashes are normalised so near-identical errors collapse.
     */
    private function fingerprint(string $level, string $message): string
    {
        $normalized = preg_replace('/\b[0-9a-f]{8,}\b/i', 'HASH', $message);
        $normalized = preg_replace('/\b\d+\b/', 'N', $normalized ?? $message);
        $normalized = substr($normalized ?? $message, 0, 200);
        return md5($level . ':' . $normalized);
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return round($bytes / 1_073_741_824, 2) . ' GB';
        }
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
