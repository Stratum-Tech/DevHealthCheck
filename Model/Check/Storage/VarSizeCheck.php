<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Storage;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class VarSizeCheck implements CheckInterface
{
    private const WARN_GB = 5;
    private const FAIL_GB = 20;

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Var Directory Size';
    }

    public function getSection(): string
    {
        return 'Storage';
    }

    public function run(): CheckResult
    {
        $varDir = $this->directoryList->getPath('var');

        if (!is_dir($varDir)) {
            return CheckResult::skip('var/ does not exist');
        }

        $sizeBytes = $this->directorySize($varDir);
        $sizeGb    = round($sizeBytes / (1024 ** 3), 1);

        if ($sizeGb >= self::FAIL_GB) {
            return CheckResult::fail(
                sprintf('%.1fGB', $sizeGb),
                'var/ is very large — clear cache, logs, and reports; check for runaway log files',
            );
        }

        if ($sizeGb >= self::WARN_GB) {
            return CheckResult::warn(
                sprintf('%.1fGB', $sizeGb),
                'Consider clearing old logs, cache, and error reports',
            );
        }

        return CheckResult::ok(sprintf('%.1fGB', $sizeGb));
    }

    private function directorySize(string $path): int
    {
        $output = shell_exec(sprintf('du -sk %s 2>/dev/null', escapeshellarg($path)));

        if ($output !== null && preg_match('/^(\d+)/', $output, $matches)) {
            return (int) $matches[1] * 1024;
        }

        // Fallback if shell_exec is unavailable
        $size     = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }
}
