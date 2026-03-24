<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Logging;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class ReportDirectoryCheck implements CheckInterface
{
    private const WARN_COUNT = 10;
    private const FAIL_COUNT = 100;

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Error Reports';
    }

    public function getSection(): string
    {
        return 'Logging';
    }

    public function run(): CheckResult
    {
        $reportDir = $this->directoryList->getPath('var') . '/report';

        if (!is_dir($reportDir)) {
            return CheckResult::ok('var/report does not exist');
        }

        $files = array_filter(
            scandir($reportDir) ?: [],
            fn(string $f) => !in_array($f, ['.', '..'], true) && is_file($reportDir . '/' . $f),
        );

        $count = count($files);

        if ($count >= self::FAIL_COUNT) {
            return CheckResult::fail(
                sprintf('%d error report files', $count),
                'Large number of unhandled exceptions — investigate var/report/',
            );
        }

        if ($count >= self::WARN_COUNT) {
            return CheckResult::warn(
                sprintf('%d error report files', $count),
                'Errors accumulating in var/report/ — review and address root causes',
            );
        }

        return CheckResult::ok(sprintf('%d error report files', $count));
    }
}
