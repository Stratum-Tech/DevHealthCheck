<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Deploy;

use Magento\Framework\App\MaintenanceMode;
use Stratum\DevHealthCheck\Model\Check\Deploy\MaintenanceModeCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MaintenanceModeCheckTest extends TestCase
{
    private MaintenanceMode|MockObject $maintenanceMode;
    private MaintenanceModeCheck $check;

    protected function setUp(): void
    {
        $this->maintenanceMode = $this->createMock(MaintenanceMode::class);
        $this->check           = new MaintenanceModeCheck($this->maintenanceMode);
    }

    public function testMaintenanceOffReturnsOk(): void
    {
        $this->maintenanceMode->method('isOn')->willReturn(false);
        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testMaintenanceOnReturnsWarn(): void
    {
        $this->maintenanceMode->method('isOn')->willReturn(true);
        $this->assertSame(StatusEnum::WARN, $this->check->run()->status);
    }
}
