<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Cache;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class RedisConnectivityCheck implements CheckInterface
{
    private const TIMEOUT_SECONDS = 2;

    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Redis Connectivity';
    }

    public function getSection(): string
    {
        return 'Cache';
    }

    public function run(): CheckResult
    {
        $options = $this->deploymentConfig->get('cache/frontend/default/backend_options');

        if ($options === null) {
            $options = $this->deploymentConfig->get('cache/frontend/default/backend_options');
        }

        // Also check page_cache
        if ($options === null) {
            $options = $this->deploymentConfig->get('cache/frontend/page_cache/backend_options');
        }

        if ($options === null) {
            return CheckResult::skip('Redis not configured');
        }

        $backend = $this->deploymentConfig->get('cache/frontend/default/backend')
            ?? $this->deploymentConfig->get('cache/frontend/page_cache/backend')
            ?? '';

        if (!str_contains((string) $backend, 'Redis') && !str_contains((string) $backend, 'redis')) {
            return CheckResult::skip('Redis not configured as cache backend');
        }

        $host = (string) ($options['server'] ?? $options['host'] ?? '127.0.0.1');
        $port = (int)    ($options['port'] ?? 6379);

        $socket = @fsockopen($host, $port, $errno, $errstr, self::TIMEOUT_SECONDS);

        if ($socket === false) {
            return CheckResult::fail(
                sprintf('cannot connect to %s:%d', $host, $port),
                $errstr,
            );
        }

        fclose($socket);

        return CheckResult::ok(sprintf('connected to %s:%d', $host, $port));
    }
}
