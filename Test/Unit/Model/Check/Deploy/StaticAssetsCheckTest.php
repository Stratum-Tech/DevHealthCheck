<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Deploy;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\Deploy\StaticAssetsCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StaticAssetsCheckTest extends TestCase
{
    private State|MockObject $appState;
    private DirectoryList|MockObject $directoryList;
    private StaticAssetsCheck $check;

    protected function setUp(): void
    {
        $this->appState      = $this->createMock(State::class);
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->check         = new StaticAssetsCheck($this->appState, $this->directoryList);
    }

    public function testSkipsInDeveloperMode(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->assertSame(StatusEnum::SKIP, $this->check->run()->status);
    }
}
