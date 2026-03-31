<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Cache;

use Magento\Framework\App\Cache\TypeListInterface;
use Stratum\DevHealthCheck\Model\Check\Cache\CacheStatusCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheStatusCheckTest extends TestCase
{
    private TypeListInterface|MockObject $cacheTypeList;
    private CacheStatusCheck $check;

    protected function setUp(): void
    {
        $this->cacheTypeList = $this->createMock(TypeListInterface::class);
        $this->check         = new CacheStatusCheck($this->cacheTypeList);
    }

    public function testOkWhenAllTypesEnabled(): void
    {
        $type = new class {
            public function getStatus(): int { return 1; }
            public function getId(): string { return 'config'; }
        };

        $this->cacheTypeList->method('getTypes')->willReturn(['config' => $type]);

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testWarnWhenTypeDisabled(): void
    {
        $type = new class {
            public function getStatus(): int { return 0; }
            public function getId(): string { return 'block_html'; }
        };

        $this->cacheTypeList->method('getTypes')->willReturn(['block_html' => $type]);

        $this->assertSame(StatusEnum::WARN, $this->check->run()->status);
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('Cache Types', $this->check->getLabel());
        $this->assertSame('Cache', $this->check->getSection());
    }
}
