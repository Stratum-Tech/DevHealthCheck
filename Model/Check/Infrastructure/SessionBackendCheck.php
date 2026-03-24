<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Infrastructure;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class SessionBackendCheck implements CheckInterface
{
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Session Backend';
    }

    public function getSection(): string
    {
        return 'Infrastructure';
    }

    public function run(): CheckResult
    {
        $saveHandler = (string) ($this->deploymentConfig->get('session/save') ?? 'files');

        return match(strtolower($saveHandler)) {
            'redis'     => CheckResult::ok('Redis'),
            'db'        => CheckResult::info('database — functional but does not scale horizontally'),
            'memcached' => CheckResult::info('Memcached'),
            'files'     => CheckResult::warn(
                'file-based sessions',
                'File sessions do not work across multiple web nodes — switch to Redis for scalable deployments',
            ),
            default => CheckResult::info(sprintf('custom handler: %s', $saveHandler)),
        };
    }
}
