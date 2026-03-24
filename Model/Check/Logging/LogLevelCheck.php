<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Logging;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class LogLevelCheck implements CheckInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'Debug Logging';
    }

    public function getSection(): string
    {
        return 'Logging';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
            return CheckResult::skip('log level check only relevant in production');
        }

        $debugEnabled = $this->scopeConfig->isSetFlag('dev/debug/debug_logging');

        if ($debugEnabled) {
            return CheckResult::warn(
                'debug logging enabled in production',
                'Disable dev/debug/debug_logging — verbose logs degrade performance and fill disk',
            );
        }

        return CheckResult::ok('debug logging disabled');
    }
}
