<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Filesystem;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class DiskSpaceCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Disk Space';
    }

    public function getSection(): string
    {
        return 'Filesystem';
    }

    public function run(): CheckResult
    {
        $root  = $this->directoryList->getRoot();
        $free  = disk_free_space($root);
        $total = disk_total_space($root);

        if ($free === false || $total === false || $total === 0.0) {
            return CheckResult::skip('unable to determine disk space');
        }

        $pctFree  = ($free / $total) * 100;
        $freeGb   = round($free / (1024 ** 3), 1);
        $totalGb  = round($total / (1024 ** 3), 1);
        $summary  = sprintf('%.1fGB free of %.1fGB (%.0f%%)', $freeGb, $totalGb, $pctFree);

        if ($pctFree < 2) {
            return CheckResult::fail($summary, 'Critical: less than 2% disk space remaining');
        }

        if ($pctFree < 10) {
            return CheckResult::warn($summary, 'Less than 10% disk space remaining');
        }

        return CheckResult::ok($summary);
    }
}
