<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Environment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Stratum\DevHealthCheck\Model\Check\Environment\BaseUrlCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseUrlCheckTest extends TestCase
{
    private ScopeConfigInterface|MockObject $scopeConfig;
    private State|MockObject $appState;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->appState    = $this->createMock(State::class);
    }

    private function makeCheck(): BaseUrlCheck
    {
        return new BaseUrlCheck($this->scopeConfig, $this->appState);
    }

    public function testOkForPublicUrlInProduction(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->scopeConfig->method('getValue')->willReturn('https://www.example.com/');

        $this->assertSame(StatusEnum::OK, $this->makeCheck()->run()->status);
    }

    public function testWarnForLocalhostInProduction(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->scopeConfig->method('getValue')->willReturn('http://localhost/');

        $this->assertSame(StatusEnum::WARN, $this->makeCheck()->run()->status);
    }

    public function testOkForLocalhostInDeveloperMode(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->scopeConfig->method('getValue')->willReturn('http://localhost/');

        $this->assertSame(StatusEnum::OK, $this->makeCheck()->run()->status);
    }

    public function testFailWhenBaseUrlEmpty(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->scopeConfig->method('getValue')->willReturn('');

        $this->assertSame(StatusEnum::FAIL, $this->makeCheck()->run()->status);
    }
}
