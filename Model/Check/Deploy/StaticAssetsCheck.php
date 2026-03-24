<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Deploy;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class StaticAssetsCheck implements CheckInterface
{
    public function __construct(
        private readonly State $appState,
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Static Assets';
    }

    public function getSection(): string
    {
        return 'Deploy & Mode';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() === State::MODE_DEVELOPER) {
            return CheckResult::skip('developer mode — static assets generated on-demand');
        }

        $staticDir = $this->directoryList->getPath('pub') . '/static';

        if (!is_dir($staticDir)) {
            return CheckResult::fail('pub/static does not exist');
        }

        $entries = glob($staticDir . '/*/*', GLOB_NOSORT);

        if ($entries === false || count($entries) === 0) {
            return CheckResult::fail(
                'pub/static appears empty',
                'Run: bin/magento setup:static-content:deploy',
            );
        }

        return CheckResult::ok(sprintf('%d locale/theme directories deployed', count($entries)));
    }
}
