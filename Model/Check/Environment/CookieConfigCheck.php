<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Environment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class CookieConfigCheck implements CheckInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'Cookie Security';
    }

    public function getSection(): string
    {
        return 'Environment';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
            return CheckResult::skip('cookie security check only relevant in production');
        }

        $httpOnly = $this->scopeConfig->isSetFlag(
            'web/cookie/cookie_httponly',
            ScopeInterface::SCOPE_STORE,
        );

        $secure = $this->scopeConfig->isSetFlag(
            'web/cookie/cookie_secure',
            ScopeInterface::SCOPE_STORE,
        );

        $issues = [];

        if (!$httpOnly) {
            $issues[] = 'HttpOnly disabled (web/cookie/cookie_httponly)';
        }

        if (!$secure) {
            $issues[] = 'Secure flag disabled (web/cookie/cookie_secure)';
        }

        if ($issues !== []) {
            return CheckResult::warn(
                sprintf('%d issue(s)', count($issues)),
                implode('; ', $issues),
            );
        }

        return CheckResult::ok('HttpOnly and Secure flags enabled');
    }
}
