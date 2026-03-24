<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Php;

use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class PhpExtensionsCheck implements CheckInterface
{
    private const REQUIRED = [
        'intl', 'soap', 'bcmath', 'pdo_mysql',
        'mbstring', 'openssl', 'zip', 'ctype', 'curl',
    ];

    private const IMAGE_EXTENSIONS = ['gd', 'imagick'];

    public function getLabel(): string
    {
        return 'PHP Extensions';
    }

    public function getSection(): string
    {
        return 'PHP';
    }

    public function run(): CheckResult
    {
        $missing = [];

        foreach (self::REQUIRED as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        $hasImage = false;
        foreach (self::IMAGE_EXTENSIONS as $ext) {
            if (extension_loaded($ext)) {
                $hasImage = true;
                break;
            }
        }

        if (!$hasImage) {
            $missing[] = 'gd or imagick';
        }

        if ($missing !== []) {
            return CheckResult::fail(
                sprintf('%d missing', count($missing)),
                implode(', ', $missing),
            );
        }

        return CheckResult::ok('all required extensions loaded');
    }
}
