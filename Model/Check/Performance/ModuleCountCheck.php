<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Performance;

use Magento\Framework\Module\ModuleListInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class ModuleCountCheck implements CheckInterface
{
    private const WARN_THRESHOLD = 300;
    private const FAIL_THRESHOLD = 400;

    public function __construct(
        private readonly ModuleListInterface $moduleList,
    ) {}

    public function getLabel(): string
    {
        return 'Module Count';
    }

    public function getSection(): string
    {
        return 'Performance';
    }

    public function run(): CheckResult
    {
        $count = count($this->moduleList->getAll());

        if ($count >= self::FAIL_THRESHOLD) {
            return CheckResult::fail(
                sprintf('%d enabled modules', $count),
                sprintf('Exceeds %d — significant DI compilation and request overhead likely', self::FAIL_THRESHOLD),
            );
        }

        if ($count >= self::WARN_THRESHOLD) {
            return CheckResult::warn(
                sprintf('%d enabled modules', $count),
                sprintf('Exceeds %d — review for unused or redundant modules', self::WARN_THRESHOLD),
            );
        }

        return CheckResult::ok(sprintf('%d enabled modules', $count));
    }
}
