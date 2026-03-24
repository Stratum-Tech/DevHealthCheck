<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Performance;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class MinifyAssetsCheck implements CheckInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'Asset Minification';
    }

    public function getSection(): string
    {
        return 'Performance';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
            return CheckResult::skip('minification check only relevant in production');
        }

        $jsMinify  = $this->scopeConfig->isSetFlag('dev/js/minify_files', ScopeInterface::SCOPE_STORE);
        $cssMinify = $this->scopeConfig->isSetFlag('dev/css/minify_files', ScopeInterface::SCOPE_STORE);
        $jsMerge   = $this->scopeConfig->isSetFlag('dev/js/merge_files', ScopeInterface::SCOPE_STORE);
        $cssMerge  = $this->scopeConfig->isSetFlag('dev/css/merge_css_files', ScopeInterface::SCOPE_STORE);

        $off = [];

        if (!$jsMinify) {
            $off[] = 'JS minify';
        }
        if (!$cssMinify) {
            $off[] = 'CSS minify';
        }
        if (!$jsMerge) {
            $off[] = 'JS merge';
        }
        if (!$cssMerge) {
            $off[] = 'CSS merge';
        }

        if ($off !== []) {
            return CheckResult::warn(
                sprintf('%d optimisation(s) disabled', count($off)),
                implode(', ', $off),
            );
        }

        return CheckResult::ok('JS and CSS minification and merging enabled');
    }
}
