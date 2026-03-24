<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Php;

use Magento\Framework\App\State;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class OpCacheCheck implements CheckInterface
{
    public function __construct(
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'OPcache';
    }

    public function getSection(): string
    {
        return 'PHP';
    }

    public function run(): CheckResult
    {
        if (!function_exists('opcache_get_configuration')) {
            return CheckResult::skip('opcache_get_configuration() not available (CLI SAPI restriction)');
        }

        $config = opcache_get_configuration();

        if ($config === false || empty($config['directives'])) {
            return CheckResult::skip('OPcache not available or not enabled');
        }

        $directives = $config['directives'];
        $warnings   = [];

        if (!($directives['opcache.enable'] ?? false)) {
            return CheckResult::warn('OPcache disabled', 'Enable opcache.enable for production performance');
        }

        $memory = (int) ($directives['opcache.memory_consumption'] ?? 0);
        if ($memory < 128) {
            $warnings[] = sprintf('opcache.memory_consumption=%dMB (recommend >=128MB)', $memory);
        }

        $isProduction = $this->appState->getMode() === State::MODE_PRODUCTION;
        if ($isProduction && ($directives['opcache.validate_timestamps'] ?? true)) {
            $warnings[] = 'opcache.validate_timestamps=1 in production (recommend 0)';
        }

        if ($warnings !== []) {
            return CheckResult::warn('configured with warnings', implode('; ', $warnings));
        }

        return CheckResult::ok(sprintf('%dMB memory', $memory));
    }
}
