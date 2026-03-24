<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cache;

use Magento\Framework\App\Cache\TypeListInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class CacheStatusCheck implements CheckInterface
{
    public function __construct(
        private readonly TypeListInterface $cacheTypeList,
    ) {}

    public function getLabel(): string
    {
        return 'Cache Types';
    }

    public function getSection(): string
    {
        return 'Cache';
    }

    public function run(): CheckResult
    {
        $types    = $this->cacheTypeList->getTypes();
        $disabled = [];

        foreach ($types as $type) {
            if (!$type->getStatus()) {
                $disabled[] = $type->getId();
            }
        }

        if ($disabled !== []) {
            return CheckResult::warn(
                sprintf('%d type(s) disabled', count($disabled)),
                implode(', ', $disabled),
            );
        }

        return CheckResult::ok(sprintf('all %d types enabled', count($types)));
    }
}
