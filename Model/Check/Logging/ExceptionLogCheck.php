<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Logging;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class ExceptionLogCheck implements CheckInterface
{
    private const WARN_SIZE_MB  = 10;
    private const FAIL_SIZE_MB  = 50;
    private const RECENT_WINDOW = 3600; // 1 hour in seconds

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Exception Log';
    }

    public function getSection(): string
    {
        return 'Logging';
    }

    public function run(): CheckResult
    {
        $logFile = $this->directoryList->getPath('var') . '/log/exception.log';

        if (!file_exists($logFile)) {
            return CheckResult::ok('exception.log does not exist (no exceptions logged)');
        }

        $sizeMb     = round(filesize($logFile) / (1024 * 1024), 1);
        $modifiedAt = filemtime($logFile);
        $ageMinutes = (int) round((time() - $modifiedAt) / 60);

        if ($sizeMb >= self::FAIL_SIZE_MB) {
            return CheckResult::fail(
                sprintf('%.1fMB — last modified %d minutes ago', $sizeMb, $ageMinutes),
                'Exception log is very large — investigate and rotate',
            );
        }

        // Recent activity (modified in last hour) is worth flagging
        $recentlyModified = (time() - $modifiedAt) < self::RECENT_WINDOW;

        if ($sizeMb >= self::WARN_SIZE_MB || $recentlyModified) {
            return CheckResult::warn(
                sprintf('%.1fMB — last modified %d minutes ago', $sizeMb, $ageMinutes),
                $recentlyModified ? 'Exceptions logged in the last hour' : 'Log file is large',
            );
        }

        return CheckResult::ok(sprintf('%.1fMB — last modified %d minutes ago', $sizeMb, $ageMinutes));
    }
}
