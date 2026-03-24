<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Infrastructure;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class RequireJsCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'RequireJS Config';
    }

    public function getSection(): string
    {
        return 'Infrastructure';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() === State::MODE_DEVELOPER) {
            return CheckResult::skip('RequireJS config generated on-demand in developer mode');
        }

        $requireJsDir = $this->directoryList->getPath('pub') . '/static/_requirejs';

        if (!is_dir($requireJsDir)) {
            return CheckResult::fail(
                'pub/static/_requirejs not found',
                'Run bin/magento setup:static-content:deploy — missing RequireJS config causes JS errors',
            );
        }

        $files = glob($requireJsDir . '/*/*.js') ?: [];

        if (count($files) === 0) {
            return CheckResult::warn(
                'pub/static/_requirejs exists but appears empty',
                'Re-run bin/magento setup:static-content:deploy',
            );
        }

        return CheckResult::ok(sprintf('%d RequireJS config file(s) deployed', count($files)));
    }
}
