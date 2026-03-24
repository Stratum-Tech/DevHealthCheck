<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Deploy;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class GeneratedDirectoryCheck implements CheckInterface
{
    public function __construct(
        private readonly State $appState,
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Generated Code';
    }

    public function getSection(): string
    {
        return 'Deploy & Mode';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() === State::MODE_DEVELOPER) {
            return CheckResult::skip('developer mode — code generated on-demand');
        }

        $generatedDir = $this->directoryList->getRoot() . '/generated/code';

        if (!is_dir($generatedDir)) {
            return CheckResult::fail(
                'generated/code does not exist',
                'Run: bin/magento setup:di:compile',
            );
        }

        $iterator = new \FilesystemIterator($generatedDir, \FilesystemIterator::SKIP_DOTS);
        if (!$iterator->valid()) {
            return CheckResult::fail(
                'generated/code is empty',
                'Run: bin/magento setup:di:compile',
            );
        }

        return CheckResult::ok('present and non-empty');
    }
}
