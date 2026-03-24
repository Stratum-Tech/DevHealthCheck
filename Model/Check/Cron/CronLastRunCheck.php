<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cron;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class CronLastRunCheck implements CheckInterface
{
    private const STALE_MINUTES = 15;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'Cron Last Run';
    }

    public function getSection(): string
    {
        return 'Cron';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();
        $table      = $this->resourceConnection->getTableName('cron_schedule');

        $lastFinished = $connection->fetchOne(
            sprintf("SELECT MAX(finished_at) FROM %s WHERE status = 'success'", $table),
        );

        if (empty($lastFinished)) {
            return CheckResult::fail('no successful cron jobs recorded');
        }

        $lastTs  = strtotime($lastFinished);
        $ageMin  = (int) round((time() - $lastTs) / 60);

        if ($ageMin > self::STALE_MINUTES) {
            return CheckResult::fail(
                sprintf('last run %d minutes ago', $ageMin),
                sprintf('Expected at least one successful job within %d minutes', self::STALE_MINUTES),
            );
        }

        return CheckResult::ok(sprintf('last run %d minutes ago', $ageMin));
    }
}
