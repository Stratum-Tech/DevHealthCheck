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
        $root   = $this->directoryList->getRoot();
        $pubDir = $this->directoryList->getPath('pub');
        $varDir = $this->directoryList->getPath('var');

        // var/ should not be inside pub/
        if (str_starts_with(realpath($varDir) ?: $varDir, realpath($pubDir) ?: $pubDir)) {
            return CheckResult::fail(
                'var/ is inside pub/',
                'var/log/ and var/report/ are web-accessible — move var/ outside pub/',
            );
        }

        // If webroot is the Magento root (not pub/), check that var/log and var/report
        // are protected by an .htaccess deny rule.
        $rootHtaccess = $root . '/.htaccess';
        $exposed      = [];

        foreach (['var/log', 'var/report'] as $dir) {
            $path         = $root . '/' . $dir;
            $dirHtaccess  = $path . '/.htaccess';

            if (!is_dir($path)) {
                continue;
            }

            $protectedByRoot = file_exists($rootHtaccess)
                && str_contains((string) file_get_contents($rootHtaccess), $dir);

            if (!file_exists($dirHtaccess) && !$protectedByRoot) {
                $exposed[] = $dir;
            }
        }

        if ($exposed !== []) {
            return CheckResult::warn(
                sprintf('%s may be web-accessible', implode(', ', $exposed)),
                'Add .htaccess deny rules or set document root to pub/',
            );
        }

        return CheckResult::ok('var/log and var/report outside pub/');
    }
}
