<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Extensions;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class RewriteCountCheck implements CheckInterface
{
    private const WARN_THRESHOLD = 20;
    private const FAIL_THRESHOLD = 50;

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Class Rewrites';
    }

    public function getSection(): string
    {
        return 'Extensions';
    }

    public function run(): CheckResult
    {
        $root  = $this->directoryList->getRoot();
        $count = 0;
        $files = 0;

        // Scan app/code and vendor for di.xml files
        $diFiles = array_merge(
            glob($root . '/app/code/*/*/etc/di.xml') ?: [],
            glob($root . '/app/code/*/*/etc/*/di.xml') ?: [],
            glob($root . '/vendor/*/*/etc/di.xml') ?: [],
            glob($root . '/vendor/*/*/etc/*/di.xml') ?: [],
        );

        foreach ($diFiles as $diFile) {
            $contents = file_get_contents($diFile);
            if ($contents === false) {
                continue;
            }
            $count += substr_count($contents, '<preference');
            $files++;
        }

        if ($count >= self::FAIL_THRESHOLD) {
            return CheckResult::fail(
                sprintf('%d <preference> rewrites across %d di.xml files', $count, $files),
                'High rewrite count increases conflict risk and debugging complexity',
            );
        }

        if ($count >= self::WARN_THRESHOLD) {
            return CheckResult::warn(
                sprintf('%d <preference> rewrites across %d di.xml files', $count, $files),
                'Review for plugins that could replace rewrites',
            );
        }

        return CheckResult::ok(sprintf('%d <preference> rewrites', $count));
    }
}
