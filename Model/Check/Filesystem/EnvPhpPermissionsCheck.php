<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Filesystem;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class EnvPhpPermissionsCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'env.php Permissions';
    }

    public function getSection(): string
    {
        return 'Filesystem';
    }

    public function run(): CheckResult
    {
        $path = $this->directoryList->getRoot() . '/app/etc/env.php';

        if (!file_exists($path)) {
            return CheckResult::fail('app/etc/env.php not found');
        }

        $perms = fileperms($path);
        $octal = decoct($perms & 0777);

        // World-readable: others have any read bit set (0004)
        if ($perms & 0004) {
            return CheckResult::warn(
                sprintf('permissions %s — world-readable', $octal),
                'Recommend chmod 640 app/etc/env.php',
            );
        }

        return CheckResult::ok(sprintf('permissions %s', $octal));
    }
}
