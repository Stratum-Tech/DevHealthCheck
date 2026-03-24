<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Extensions;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class DuplicateModuleCheck implements CheckInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'Duplicate Modules';
    }

    public function getSection(): string
    {
        return 'Extensions';
    }

    public function run(): CheckResult
    {
        $root = $this->directoryList->getRoot();

        $appCodeModules = $this->collectModuleNames($root . '/app/code/*/*/etc/module.xml');
        $vendorModules  = $this->collectModuleNames($root . '/vendor/*/*/etc/module.xml');

        $duplicates = array_intersect($appCodeModules, $vendorModules);

        if ($duplicates !== []) {
            return CheckResult::fail(
                sprintf('%d duplicate module(s)', count($duplicates)),
                implode(', ', $duplicates) . ' — found in both app/code/ and vendor/',
            );
        }

        return CheckResult::ok('no identity conflicts between app/code/ and vendor/');
    }

    private function collectModuleNames(string $pattern): array
    {
        $names = [];

        foreach (glob($pattern) ?: [] as $file) {
            try {
                $xml = simplexml_load_file($file);
                if ($xml === false) {
                    continue;
                }
                $name = (string) ($xml->module['name'] ?? '');
                if ($name !== '') {
                    $names[] = $name;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $names;
    }
}
