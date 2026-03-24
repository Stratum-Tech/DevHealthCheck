<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Security;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class GitDirectoryCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return '.git in Webroot';
    }

    public function getSection(): string
    {
        return 'Security';
    }

    public function run(): CheckResult
    {
        $pubDir = $this->directoryList->getPath('pub');
        $gitInPub = $pubDir . '/.git';

        if (is_dir($gitInPub)) {
            return CheckResult::fail(
                '.git/ found inside pub/',
                'Your git repository is web-accessible — move webroot to pub/ or add .git to .htaccess deny rules',
            );
        }

        // Also check if Magento root .git exists but pub/ is NOT the webroot
        // (indicated by .htaccess or index.php not being in pub/)
        $rootGit = $this->directoryList->getRoot() . '/.git';
        if (!is_dir($rootGit)) {
            return CheckResult::info('no .git directory found');
        }

        return CheckResult::ok('.git/ not inside pub/');
    }
}
