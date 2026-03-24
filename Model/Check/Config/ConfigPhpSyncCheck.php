<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Config;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleListInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class ConfigPhpSyncCheck implements CheckInterface
{
    public function __construct(
        private readonly FullModuleList $fullModuleList,
        private readonly ModuleListInterface $enabledModuleList,
    ) {}

    public function getLabel(): string
    {
        return 'config.php Sync';
    }

    public function getSection(): string
    {
        return 'Config Integrity';
    }

    public function run(): CheckResult
    {
        $allModules     = array_keys($this->fullModuleList->getAll());
        $enabledModules = array_keys($this->enabledModuleList->getAll());

        // Modules that are registered but not in config.php (neither enabled nor explicitly disabled)
        // These are modules installed after the last setup:upgrade --no-interaction run
        $notInConfig = array_diff($allModules, $enabledModules);

        // Filter out modules that are in full list but we expect them to be absent
        // (This catch covers modules registered but not yet setup:upgrade'd)
        if (count($notInConfig) > 0) {
            return CheckResult::warn(
                sprintf('%d module(s) not in config.php', count($notInConfig)),
                implode(', ', array_slice(array_values($notInConfig), 0, 5))
                    . (count($notInConfig) > 5 ? '...' : '')
                    . ' — run bin/magento setup:upgrade',
            );
        }

        return CheckResult::ok(sprintf('all %d modules accounted for', count($allModules)));
    }
}
