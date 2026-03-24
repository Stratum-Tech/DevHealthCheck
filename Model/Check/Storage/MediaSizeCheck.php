<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Storage;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class MediaSizeCheck implements CheckInterface
{
    private const WARN_GB = 20;
    private const FAIL_GB = 50;

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Media Size';
    }

    public function getSection(): string
    {
        return 'Storage';
    }

    public function run(): CheckResult
    {
        $mediaDir = $this->directoryList->getPath('pub') . '/media';

        if (!is_dir($mediaDir)) {
            return CheckResult::skip('pub/media does not exist');
        }

        $sizeBytes = $this->directorySize($mediaDir);
        $sizeGb    = round($sizeBytes / (1024 ** 3), 1);

        if ($sizeGb >= self::FAIL_GB) {
            return CheckResult::fail(
                sprintf('%.1fGB', $sizeGb),
                'pub/media is very large — review image optimisation, remove orphaned files, or move to remote storage',
            );
        }

        if ($sizeGb >= self::WARN_GB) {
            return CheckResult::warn(
                sprintf('%.1fGB', $sizeGb),
                'Consider remote storage (S3/GCS) or image optimisation',
            );
        }

        return CheckResult::ok(sprintf('%.1fGB', $sizeGb));
    }

    private function directorySize(string $path): int
    {
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
