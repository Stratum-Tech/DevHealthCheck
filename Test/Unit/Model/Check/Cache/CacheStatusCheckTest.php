<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
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
        $type = $this->createMock(\Magento\Framework\Cache\Frontend\Decorator\TagScope::class);
        $type->method('getStatus')->willReturn(1);
        $type->method('getId')->willReturn('config');

        $this->cacheTypeList->method('getTypes')->willReturn(['config' => $type]);

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testWarnWhenTypeDisabled(): void
    {
        $type = $this->createMock(\Magento\Framework\Cache\Frontend\Decorator\TagScope::class);
        $type->method('getStatus')->willReturn(0);
        $type->method('getId')->willReturn('block_html');

        $this->cacheTypeList->method('getTypes')->willReturn(['block_html' => $type]);

        $this->assertSame(StatusEnum::WARN, $this->check->run()->status);
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('Cache Types', $this->check->getLabel());
        $this->assertSame('Cache', $this->check->getSection());
    }
}
