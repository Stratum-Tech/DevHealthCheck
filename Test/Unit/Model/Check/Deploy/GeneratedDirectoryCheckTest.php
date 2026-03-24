<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Deploy;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\Deploy\GeneratedDirectoryCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneratedDirectoryCheckTest extends TestCase
{
    private State|MockObject $appState;
    private DirectoryList|MockObject $directoryList;
    private GeneratedDirectoryCheck $check;

    protected function setUp(): void
    {
        $this->appState      = $this->createMock(State::class);
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->check         = new GeneratedDirectoryCheck($this->appState, $this->directoryList);
    }

    public function testSkipsInDeveloperMode(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->assertSame(StatusEnum::SKIP, $this->check->run()->status);
    }

    public function testFailsWhenGeneratedDirectoryMissing(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->directoryList->method('getRoot')->willReturn('/nonexistent/path/magento');

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }
}
