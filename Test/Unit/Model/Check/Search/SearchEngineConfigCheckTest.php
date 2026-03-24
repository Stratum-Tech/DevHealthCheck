<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Stratum\DevHealthCheck\Model\Check\Search\SearchEngineConfigCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchEngineConfigCheckTest extends TestCase
{
    private ScopeConfigInterface|MockObject $scopeConfig;
    private SearchEngineConfigCheck $check;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->check       = new SearchEngineConfigCheck($this->scopeConfig);
    }

    public function testOkForElasticsearch8(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn('elasticsearch8');

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testOkForOpenSearch(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn('opensearch');

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testWarnForMysqlEngine(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn('mysql');

        $this->assertSame(StatusEnum::WARN, $this->check->run()->status);
    }

    public function testFailWhenEngineNotConfigured(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('catalog/search/engine')
            ->willReturn(null);

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }
}
