<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Environment;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\Environment\CryptKeyCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CryptKeyCheckTest extends TestCase
{
    private DeploymentConfig|MockObject $deploymentConfig;
    private CryptKeyCheck $check;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->check            = new CryptKeyCheck($this->deploymentConfig);
    }

    public function testOkWhenKeyPresent(): void
    {
        $this->deploymentConfig->method('get')
            ->with('crypt/key')
            ->willReturn('abc123secretkey');

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testFailWhenKeyMissing(): void
    {
        $this->deploymentConfig->method('get')
            ->with('crypt/key')
            ->willReturn(null);

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }

    public function testFailWhenKeyEmpty(): void
    {
        $this->deploymentConfig->method('get')
            ->with('crypt/key')
            ->willReturn('');

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }
}
