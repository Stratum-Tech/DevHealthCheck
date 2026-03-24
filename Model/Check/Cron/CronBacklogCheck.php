<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cron;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class CronBacklogCheck implements CheckInterface
{
    private const STALE_MINUTES  = 5;
    private const WARN_THRESHOLD = 10;
    private const FAIL_THRESHOLD = 50;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'Cron Backlog';
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
                "SELECT COUNT(*) FROM %s WHERE status = 'pending' AND scheduled_at <= DATE_SUB(NOW(), INTERVAL %d MINUTE)",
                $table,
                self::STALE_MINUTES,
            ),
        );

        if ($count >= self::FAIL_THRESHOLD) {
            return CheckResult::fail(
                sprintf('%d pending jobs older than %d minutes', $count, self::STALE_MINUTES),
                'Cron may not be running — check cron setup',
            );
        }

        if ($count >= self::WARN_THRESHOLD) {
            return CheckResult::warn(
                sprintf('%d pending jobs older than %d minutes', $count, self::STALE_MINUTES),
                'Cron may be falling behind',
            );
        }

        return CheckResult::ok(sprintf('%d stale pending jobs', $count));
    }
}
