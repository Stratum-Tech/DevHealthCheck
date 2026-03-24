<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Deploy;

use Magento\Framework\App\State;
use Stratum\DevHealthCheck\Model\Check\Deploy\DeployModeCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeployModeCheckTest extends TestCase
{
    private State|MockObject $appState;
    private DeployModeCheck $check;

    protected function setUp(): void
    {
        $this->appState = $this->createMock(State::class);
        $this->check    = new DeployModeCheck($this->appState);
    }

    public function testProductionModeReturnsOk(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testDeveloperModeReturnsWarn(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->assertSame(StatusEnum::WARN, $this->check->run()->status);
    }

    public function testDefaultModeReturnsFail(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_DEFAULT);
        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }

    public function testUnknownModeReturnsInfo(): void
    {
        $this->appState->method('getMode')->willReturn('custom');
        $this->assertSame(StatusEnum::INFO, $this->check->run()->status);
    }
}
