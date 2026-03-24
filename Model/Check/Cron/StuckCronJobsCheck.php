<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cron;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class StuckCronJobsCheck implements CheckInterface
{
    private const STUCK_MINUTES = 30;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'Stuck Cron Jobs';
    }

    public function getSection(): string
    {
        return 'Cron';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();
        $table      = $this->resourceConnection->getTableName('cron_schedule');

        $rows = $connection->fetchAll(
            sprintf(
                "SELECT job_code, executed_at FROM %s
                 WHERE status = 'running'
                 AND executed_at <= DATE_SUB(NOW(), INTERVAL %d MINUTE)",
                $table,
                self::STUCK_MINUTES,
            ),
        );

        if (empty($rows)) {
            return CheckResult::ok('no stuck jobs detected');
        }

        $jobs = array_column($rows, 'job_code');

        return CheckResult::fail(
            sprintf('%d job(s) stuck in "running" state', count($rows)),
            implode(', ', $jobs) . sprintf(' — running for >%d minutes', self::STUCK_MINUTES),
        );
    }
}
