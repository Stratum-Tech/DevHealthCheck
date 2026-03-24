<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Performance;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class FullPageCacheCheck implements CheckInterface
{
    // Magento\PageCache\Model\Config::BUILT_IN = 1, VARNISH = 2
    private const BUILT_IN = '1';
    private const VARNISH  = '2';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'Full Page Cache';
    }

    public function getSection(): string
    {
        return 'Performance';
    }

    public function run(): CheckResult
    {
        $type = (string) $this->scopeConfig->getValue('system/full_page_cache/caching_application');

        if ($type === self::VARNISH) {
            return CheckResult::ok('Varnish');
        }

        if ($type === self::BUILT_IN) {
            if ($this->appState->getMode() === State::MODE_PRODUCTION) {
                return CheckResult::warn(
                    'built-in (PHP)',
                    'Consider Varnish for high-traffic production — built-in FPC has higher PHP overhead',
                );
            }
            return CheckResult::info('built-in (PHP)');
        }

        return CheckResult::fail(
            'FPC not configured',
            'Enable full page cache for production performance',
        );
    }
}
