<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Deploy;

use Magento\Framework\App\MaintenanceMode;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class MaintenanceModeCheck implements CheckInterface
{
    public function __construct(
        private readonly MaintenanceMode $maintenanceMode,
    ) {}

    public function getLabel(): string
    {
        return 'Maintenance Mode';
    }

    public function getSection(): string
    {
        return 'Deploy & Mode';
    }

    public function run(): CheckResult
    {
        if ($this->maintenanceMode->isOn()) {
            return CheckResult::warn('enabled', 'Site is in maintenance mode — visitors see the maintenance page');
        }

        return CheckResult::ok('disabled');
    }
}
