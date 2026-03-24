<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Config;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\FullModuleList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class ConfigPhpSyncCheck implements CheckInterface
{
    public function __construct(
        private readonly FullModuleList $fullModuleList,
        private readonly DeploymentConfig $deploymentConfig,
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
        $configModules = $this->deploymentConfig->get('modules');

        if (!is_array($configModules)) {
            return CheckResult::warn(
                'config.php missing modules section',
                'Run bin/magento app:config:dump to generate it',
            );
        }

        $allModules  = array_keys($this->fullModuleList->getAll());
        $notInConfig = array_diff($allModules, array_keys($configModules));

        if ($notInConfig !== []) {
            $preview = implode(', ', array_slice(array_values($notInConfig), 0, 5));
            $suffix  = count($notInConfig) > 5 ? '...' : '';

            return CheckResult::warn(
                sprintf('%d module(s) missing from config.php', count($notInConfig)),
                $preview . $suffix . ' — run bin/magento app:config:dump',
            );
        }

        return CheckResult::ok(sprintf('all %d modules present in config.php', count($allModules)));
    }
}
