<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Security;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class LogDirectoryCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Log Dir Exposure';
    }

    public function getSection(): string
    {
        return 'Security';
    }

    public function run(): CheckResult
    {
        $root    = $this->directoryList->getRoot();
        $pubDir  = $this->directoryList->getPath('pub');
        $varDir  = $this->directoryList->getPath('var');

        // var/ should not be inside pub/
        if (str_starts_with(realpath($varDir) ?: $varDir, realpath($pubDir) ?: $pubDir)) {
            return CheckResult::fail(
                'var/ is inside pub/',
                'var/log/ and var/report/ are web-accessible — move var/ outside pub/',
            );
        }

        // Check for .htaccess protection on var/ in case webroot is Magento root
        $exposed = [];

        foreach (['var/log', 'var/report'] as $dir) {
            $path = $root . '/' . $dir;
            if (is_dir($path) && !file_exists($path . '/.htaccess') && !file_exists($root . '/.htaccess')) {
                $exposed[] = $dir;
            }
        }

        // If webroot is pub/, var/ is already protected — this is informational
        return CheckResult::ok('var/log and var/report outside pub/');
    }
}
