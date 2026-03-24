<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cron;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class DuplicateCronScheduleCheck implements CheckInterface
{
    private const WARN_THRESHOLD = 5;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'Duplicate Cron Entries';
    }

    public function getSection(): string
    {
        return 'Cron';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();
        $table      = $this->resourceConnection->getTableName('cron_schedule');

        // Find job_code + scheduled_at combinations with more than one pending entry
        $rows = $connection->fetchAll(
            sprintf(
                "SELECT job_code, scheduled_at, COUNT(*) as cnt
                 FROM %s
                 WHERE status = 'pending'
                 GROUP BY job_code, scheduled_at
                 HAVING cnt > 1
                 ORDER BY cnt DESC
                 LIMIT 10",
                $table,
            ),
        );

        if (empty($rows)) {
            return CheckResult::ok('no duplicate scheduled entries');
        }

        $total = array_sum(array_column($rows, 'cnt')) - count($rows);
        $jobs  = array_unique(array_column($rows, 'job_code'));

        if (count($rows) >= self::WARN_THRESHOLD) {
            return CheckResult::warn(
                sprintf('%d duplicate cron entries across %d job(s)', $total, count($jobs)),
                implode(', ', $jobs) . ' — cron tab may be running too frequently',
            );
        }

        return CheckResult::warn(
            sprintf('%d duplicate cron entries', $total),
            implode(', ', $jobs),
        );
    }
}
