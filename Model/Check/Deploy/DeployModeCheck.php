<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Deploy;

use Magento\Framework\App\State;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class DeployModeCheck implements CheckInterface
{
    public function __construct(
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'Deploy Mode';
    }

    public function getSection(): string
    {
        return 'Deploy & Mode';
    }

    public function run(): CheckResult
    {
        $mode = $this->appState->getMode();

        return match($mode) {
            State::MODE_PRODUCTION => CheckResult::ok('production'),
            State::MODE_DEVELOPER  => CheckResult::warn('developer — not suitable for production'),
            State::MODE_DEFAULT    => CheckResult::fail('default — configure deploy mode explicitly'),
            default                => CheckResult::info(sprintf('unknown mode: %s', $mode)),
        };
    }
}
