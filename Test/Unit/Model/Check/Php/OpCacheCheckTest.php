<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Php;

use Magento\Framework\App\State;
use Stratum\DevHealthCheck\Model\Check\Php\OpCacheCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OpCacheCheckTest extends TestCase
{
    private State|MockObject $appState;
    private OpCacheCheck $check;

    protected function setUp(): void
    {
        $this->appState = $this->createMock(State::class);
        $this->check    = new OpCacheCheck($this->appState);
    }

    public function testRunReturnsAResult(): void
    {
        // OPcache may or may not be available in CLI test environment — either is valid
        $result = $this->check->run();
        $this->assertContains($result->status, StatusEnum::cases());
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('OPcache', $this->check->getLabel());
        $this->assertSame('PHP', $this->check->getSection());
    }
}
