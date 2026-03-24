<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Security;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class WebRootCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Webroot Location';
    }

    public function getSection(): string
    {
        return 'Security';
    }

    public function run(): CheckResult
    {
        $root   = $this->directoryList->getRoot();
        $pubDir = $this->directoryList->getPath('pub');

        // If sensitive files are reachable from pub/, webroot may be Magento root
        $sensitiveFiles = [
            $root . '/app/etc/env.php',
            $root . '/composer.json',
        ];

        // Check if pub/ has its own index.php (expected for correct setup)
        if (!file_exists($pubDir . '/index.php')) {
            return CheckResult::fail(
                'pub/index.php not found',
                'pub/ directory may not be configured as the webroot',
            );
        }

        // Check that sensitive files don't live inside pub/
        foreach ($sensitiveFiles as $file) {
            if (str_starts_with($file, $pubDir)) {
                return CheckResult::fail(
                    'sensitive files inside pub/',
                    sprintf('%s is web-accessible', $file),
                );
            }
        }

        // Cannot determine actual server document root from CLI — flag for manual verification
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;

        if ($docRoot !== null) {
            $realDocRoot = realpath($docRoot);
            $realPub     = realpath($pubDir);

            if ($realDocRoot !== $realPub) {
                return CheckResult::warn(
                    sprintf('document root is %s, not pub/', $docRoot),
                    'Set your web server document root to pub/ to prevent exposing application files',
                );
            }

            return CheckResult::ok(sprintf('document root correctly set to pub/'));
        }

        return CheckResult::skip('document root not available from CLI');
    }
}
