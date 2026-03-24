<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Filesystem;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class WritableDirectoriesCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Writable Directories';
    }

    public function getSection(): string
    {
        return 'Filesystem';
    }

    public function run(): CheckResult
    {
        $root = $this->directoryList->getRoot();
        $pub  = $this->directoryList->getPath('pub');

        $dirs = [
            'var'         => $root . '/var',
            'pub/media'   => $pub . '/media',
            'pub/static'  => $pub . '/static',
            'generated'   => $root . '/generated',
        ];

        $notWritable = [];
        foreach ($dirs as $label => $path) {
            if (!is_writable($path)) {
                $notWritable[] = $label;
            }
        }

        if ($notWritable !== []) {
            return CheckResult::fail(
                sprintf('%d not writable', count($notWritable)),
                implode(', ', $notWritable),
            );
        }

        return CheckResult::ok('var, pub/media, pub/static, generated');
    }
}
