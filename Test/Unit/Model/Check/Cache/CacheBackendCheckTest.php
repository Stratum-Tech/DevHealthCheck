<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Cache;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\Cache\CacheBackendCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheBackendCheckTest extends TestCase
{
    private DeploymentConfig|MockObject $deploymentConfig;
    private CacheBackendCheck $check;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->check            = new CacheBackendCheck($this->deploymentConfig);
    }

    public function testOkForRedisBackend(): void
    {
        $this->deploymentConfig->method('get')
            ->willReturnMap([
                ['cache/frontend/default/backend', null, 'Cm_Cache_Backend_Redis'],
                ['cache/backend', null, null],
            ]);

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testInfoWhenNoBackendConfigured(): void
    {
        $this->deploymentConfig->method('get')->willReturn(null);

        $this->assertSame(StatusEnum::INFO, $this->check->run()->status);
    }

    public function testWarnForNonRedisBackend(): void
    {
        $this->deploymentConfig->method('get')
            ->willReturnMap([
                ['cache/frontend/default/backend', null, 'Zend_Cache_Backend_File'],
                ['cache/backend', null, null],
            ]);

        $this->assertSame(StatusEnum::WARN, $this->check->run()->status);
    }
}
