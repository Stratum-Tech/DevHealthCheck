<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cache;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class CacheBackendCheck implements CheckInterface
{
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Cache Backend';
    }

    public function getSection(): string
    {
        return 'Cache';
    }

    public function run(): CheckResult
    {
        $backend = $this->deploymentConfig->get('cache/frontend/default/backend')
            ?? $this->deploymentConfig->get('cache/backend');

        if ($backend === null) {
            return CheckResult::info('using default file-based cache backend');
        }

        $shortName = class_exists($backend) ? (new \ReflectionClass($backend))->getShortName() : $backend;

        $redisBackends = ['Cm_Cache_Backend_Redis', 'Redis'];
        $isRedis = false;
        foreach ($redisBackends as $r) {
            if (str_contains($backend, $r)) {
                $isRedis = true;
                break;
            }
        }

        if ($isRedis) {
            return CheckResult::ok(sprintf('Redis (%s)', $shortName));
        }

        return CheckResult::warn(
            sprintf('%s', $shortName),
            'Consider Redis for production cache backend',
        );
    }
}
