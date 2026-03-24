<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Environment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class HttpsRedirectCheck implements CheckInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'HTTPS Redirect';
    }

    public function getSection(): string
    {
        return 'Environment';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
            return CheckResult::skip('HTTPS check only relevant in production');
        }

        $frontend = $this->scopeConfig->isSetFlag(
            'web/secure/use_in_frontend',
            ScopeInterface::SCOPE_STORE,
        );

        $adminhtml = $this->scopeConfig->isSetFlag(
            'web/secure/use_in_adminhtml',
            ScopeInterface::SCOPE_STORE,
        );

        $off = [];

        if (!$frontend) {
            $off[] = 'frontend (web/secure/use_in_frontend)';
        }

        if (!$adminhtml) {
            $off[] = 'admin (web/secure/use_in_adminhtml)';
        }

        if ($off !== []) {
            return CheckResult::warn(
                sprintf('HTTPS off for: %s', implode(', ', $off)),
                'Enable HTTPS redirect for production',
            );
        }

        return CheckResult::ok('enabled for frontend and admin');
    }
}
