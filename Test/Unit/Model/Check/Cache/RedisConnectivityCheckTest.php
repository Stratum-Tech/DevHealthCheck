<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Cache;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\Cache\RedisConnectivityCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RedisConnectivityCheckTest extends TestCase
{
    private DeploymentConfig|MockObject $deploymentConfig;
    private RedisConnectivityCheck $check;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->check            = new RedisConnectivityCheck($this->deploymentConfig);
    }

    public function testSkipsWhenRedisNotConfigured(): void
    {
        $this->deploymentConfig->method('get')->willReturn(null);

        $this->assertSame(StatusEnum::SKIP, $this->check->run()->status);
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('Redis Connectivity', $this->check->getLabel());
        $this->assertSame('Cache', $this->check->getSection());
    }
}
