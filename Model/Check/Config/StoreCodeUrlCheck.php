<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class StoreCodeUrlCheck implements CheckInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Store Code in URL';
    }

    public function getSection(): string
    {
        return 'Config Integrity';
    }

    public function run(): CheckResult
    {
        $enabled = $this->scopeConfig->isSetFlag('web/url/use_store');

        if ($enabled) {
            return CheckResult::warn(
                'web/url/use_store is enabled',
                'Store code in URLs breaks Varnish/CDN caching when multiple stores share a domain — disable unless required',
            );
        }

        return CheckResult::ok('disabled');
    }
}
