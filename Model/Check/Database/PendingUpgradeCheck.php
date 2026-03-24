<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Database;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleListInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class PendingUpgradeCheck implements CheckInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly ModuleListInterface $moduleList,
    ) {}

    public function getLabel(): string
    {
        return 'Pending Upgrades';
    }

    public function getSection(): string
    {
        return 'Database';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();
        $table      = $this->resourceConnection->getTableName('setup_module');

        $rows = $connection->fetchAll(
            sprintf('SELECT module, schema_version FROM %s', $table),
        );

        $installed = [];
        foreach ($rows as $row) {
            $installed[$row['module']] = $row['schema_version'];
        }

        $pending = [];
        foreach ($this->moduleList->getAll() as $moduleName => $moduleInfo) {
            $setupVersion    = $moduleInfo['setup_version'] ?? null;
            $installedVersion = $installed[$moduleName] ?? null;

            if ($setupVersion === null || $installedVersion === null) {
                continue;
            }

            if (version_compare($setupVersion, $installedVersion, '>')) {
                $pending[] = sprintf('%s (%s → %s)', $moduleName, $installedVersion, $setupVersion);
            }
        }

        if ($pending !== []) {
            return CheckResult::fail(
                sprintf('%d module(s) need upgrade', count($pending)),
                implode(', ', array_slice($pending, 0, 5)) . (count($pending) > 5 ? '...' : ''),
            );
        }

        return CheckResult::ok('all modules up to date');
    }
}
