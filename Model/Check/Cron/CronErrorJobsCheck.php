<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cron;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class CronErrorJobsCheck implements CheckInterface
{
    private const WINDOW_MINUTES = 60;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'Cron Error Jobs';
    }

    public function getSection(): string
    {
        return 'Cron';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();
        $table      = $this->resourceConnection->getTableName('cron_schedule');

        $count = (int) $connection->fetchOne(
            sprintf(
                "SELECT COUNT(*) FROM %s WHERE status = 'error' AND finished_at >= DATE_SUB(NOW(), INTERVAL %d MINUTE)",
                $table,
                self::WINDOW_MINUTES,
            ),
        );

        if ($count === 0) {
            return CheckResult::ok(sprintf('no errors in last %d minutes', self::WINDOW_MINUTES));
        }

        $topJobs = $connection->fetchCol(
            sprintf(
                "SELECT DISTINCT job_code FROM %s WHERE status = 'error' AND finished_at >= DATE_SUB(NOW(), INTERVAL %d MINUTE) LIMIT 5",
                $table,
                self::WINDOW_MINUTES,
            ),
        );

        return CheckResult::warn(
            sprintf('%d error(s) in last %d minutes', $count, self::WINDOW_MINUTES),
            implode(', ', $topJobs),
        );
    }
}
