<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Security;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class TwoFactorAuthCheck implements CheckInterface
{
    private const MODULE_NAME   = 'Magento_TwoFactorAuth';
    private const BYPASS_CONFIG = 'twofactorauth/general/force_providers';

    public function __construct(
        private readonly ModuleListInterface $moduleList,
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Two-Factor Auth';
    }

    public function getSection(): string
    {
        return 'Security';
    }

    public function run(): CheckResult
    {
        if (!$this->moduleList->has(self::MODULE_NAME)) {
            return CheckResult::fail(
                'Magento_TwoFactorAuth is disabled',
                'Enable 2FA to protect your admin panel',
            );
        }

        $providers = $this->scopeConfig->getValue(self::BYPASS_CONFIG);

        if (empty($providers)) {
            return CheckResult::warn(
                'enabled but no providers configured',
                'Set twofactorauth/general/force_providers to require a specific 2FA provider',
            );
        }

        return CheckResult::ok(sprintf('enabled (%s)', $providers));
    }
}
