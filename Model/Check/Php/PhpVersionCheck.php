<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Php;

use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class PhpVersionCheck implements CheckInterface
{
    public function getLabel(): string
    {
        return 'PHP Version';
    }

    public function getSection(): string
    {
        return 'PHP';
    }

    public function run(): CheckResult
    {
        $version = PHP_VERSION;

        if (version_compare($version, '8.3.0', '>=')) {
            return CheckResult::ok($version);
        }

        if (version_compare($version, '8.2.0', '>=')) {
            return CheckResult::warn($version, 'PHP 8.2 is supported but 8.3+ is recommended');
        }

        return CheckResult::fail($version, 'Magento 2.4.x requires PHP >= 8.2');
    }
}
