<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Log;

use Magento\Framework\App\Filesystem\DirectoryList;

class LogParser
{
    private const LEVEL_ORDER = [
        'DEBUG'     => 0,
        'INFO'      => 1,
        'NOTICE'    => 2,
        'WARNING'   => 3,
        'ERROR'     => 4,
        'CRITICAL'  => 5,
        'ALERT'     => 6,
        'EMERGENCY' => 7,
    ];

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    /**
     * Parse a log file and return matching entries.
     *
     * @param string $filename Filename relative to var/log/, or an absolute path
     * @param int $maxLines Max lines to read from end of file (0 = all)
     * @param string $minLevel Minimum log level to include
     * @param \DateTimeImmutable|null $since Only include entries after this time
     * @return LogEntry[]
     */
    public function parse(
        string $filename,
        int $maxLines = 2000,
        string $minLevel = 'ERROR',
        ?\DateTimeImmutable $since = null
    ): array {
        $path = $this->resolvePath($filename);

        if (!file_exists($path) || !is_readable($path)) {
            return [];
        }

        $lines = $this->readLastLines($path, $maxLines);
        $minLevelIndex = self::LEVEL_ORDER[strtoupper($minLevel)] ?? 4;
        $entries = [];
        $buffer = '';

        foreach ($lines as $line) {
            if ($this->isLogLineStart($line)) {
                if ($buffer !== '') {
                    $entry = $this->parseLine($buffer, $filename);
                    if ($entry && $this->shouldInclude($entry, $minLevelIndex, $since)) {
                        $entries[] = $entry;
                    }
                }
                $buffer = $line;
            } else {
                $buffer .= "\n" . $line;
            }
        }

        if ($buffer !== '') {
            $entry = $this->parseLine($buffer, $filename);
            if ($entry && $this->shouldInclude($entry, $minLevelIndex, $since)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    private function resolvePath(string $filename): string
    {
        if (str_starts_with($filename, '/')) {
            return $filename;
        }
        return $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/log/' . $filename;
    }

    /**
     * Read the last $maxLines lines from a file efficiently by scanning from EOF.
     *
     * @return string[]
     */
    private function readLastLines(string $path, int $maxLines): array
    {
        if ($maxLines === 0) {
            return file($path, FILE_IGNORE_NEW_LINES) ?: [];
        }

        $handle = fopen($path, 'rb');
        if (!$handle) {
            return [];
        }

        fseek($handle, 0, SEEK_END);
        $fileSize = ftell($handle);

        if ($fileSize === 0) {
            fclose($handle);
            return [];
        }

        $content = '';
        $pos = $fileSize;
        $chunkSize = 8192;

        // Read backward in chunks until we have enough newlines
        while ($pos > 0 && substr_count($content, "\n") <= $maxLines) {
            $readSize = min($chunkSize, $pos);
            $pos -= $readSize;
            fseek($handle, $pos);
            $content = fread($handle, $readSize) . $content;
        }

        fclose($handle);

        $lines = explode("\n", $content);

        // Drop trailing empty element from files ending with a newline
        if (end($lines) === '') {
            array_pop($lines);
        }

        return array_slice($lines, -$maxLines);
    }

    private function isLogLineStart(string $line): bool
    {
        return (bool) preg_match('/^\[\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/', $line);
    }

    private function parseLine(string $line, string $filename): ?LogEntry
    {
        // Monolog format: [YYYY-MM-DD HH:MM:SS] channel.LEVEL: message {context} {extra}
        if (!preg_match(
            '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\] \S+\.(\w+): (.*)/s',
            $line,
            $m
        )) {
            return null;
        }

        try {
            $timestamp = new \DateTimeImmutable($m[1]);
        } catch (\Exception) {
            return null;
        }

        $level = strtoupper($m[2]);
        $message = $this->extractMessage($m[3]);

        // Pull exception class from context JSON, e.g. [object] (Vendor\Class(code:...)
        $exceptionClass = null;
        if (preg_match('/\[object\] \(([\\\\a-zA-Z0-9_]+)\(/', $line, $em)) {
            $exceptionClass = $em[1];
        }

        return new LogEntry(
            file: basename($filename),
            timestamp: $timestamp,
            level: $level,
            message: $message,
            exceptionClass: $exceptionClass,
        );
    }

    /**
     * Strip trailing Monolog context/extra JSON blobs and truncate long messages.
     */
    private function extractMessage(string $raw): string
    {
        // Remove trailing " {…} []" or " [] []" context/extra suffixes
        $message = preg_replace('/\s*(\{.*\}|\[\])\s*(\[\])?$/s', '', $raw) ?? $raw;
        $message = trim($message);

        if (mb_strlen($message) > 500) {
            $message = mb_substr($message, 0, 500) . '…';
        }

        return $message;
    }

    private function shouldInclude(LogEntry $entry, int $minLevelIndex, ?\DateTimeImmutable $since): bool
    {
        $levelIndex = self::LEVEL_ORDER[$entry->level] ?? 0;
        if ($levelIndex < $minLevelIndex) {
            return false;
        }
        if ($since !== null && $entry->timestamp < $since) {
            return false;
        }
        return true;
    }
}
