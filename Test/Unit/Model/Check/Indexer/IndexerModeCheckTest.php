<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Indexer;

use Magento\Framework\App\State;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Stratum\DevHealthCheck\Model\Check\Indexer\IndexerModeCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexerModeCheckTest extends TestCase
{
    private CollectionFactory|MockObject $collectionFactory;
    private State|MockObject $appState;
    private IndexerModeCheck $check;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->appState          = $this->createMock(State::class);
        $this->check             = new IndexerModeCheck($this->collectionFactory, $this->appState);
    }

    public function testSkipsOutsideProductionMode(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_DEVELOPER);

        $this->assertSame(StatusEnum::SKIP, $this->check->run()->status);
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('Indexer Mode', $this->check->getLabel());
        $this->assertSame('Indexers', $this->check->getSection());
    }
}
