<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Environment;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\Environment\AdminUrlCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdminUrlCheckTest extends TestCase
{
    private DeploymentConfig|MockObject $deploymentConfig;
    private AdminUrlCheck $check;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->check            = new AdminUrlCheck($this->deploymentConfig);
    }

    public function testOkForCustomAdminPath(): void
    {
        $this->deploymentConfig->method('get')
            ->with('backend/frontName')
            ->willReturn('my_store_admin_x9k2');

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testWarnForObviousDefaultPath(): void
    {
        foreach (['admin', 'backend', 'admin123', 'administrator'] as $default) {
            $this->deploymentConfig->method('get')
                ->with('backend/frontName')
                ->willReturn($default);

            $check = new AdminUrlCheck($this->deploymentConfig);
            $this->assertSame(StatusEnum::WARN, $check->run()->status, "Expected WARN for '{$default}'");
        }
    }

    public function testFailWhenFrontNameMissing(): void
    {
        $this->deploymentConfig->method('get')
            ->with('backend/frontName')
            ->willReturn(null);

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }
}
