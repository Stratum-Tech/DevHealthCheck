<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Stratum\DevHealthCheck\Model\Check\Cron\CronLastRunCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronLastRunCheckTest extends TestCase
{
    private ResourceConnection|MockObject $resourceConnection;
    private AdapterInterface|MockObject $connection;
    private CronLastRunCheck $check;

    protected function setUp(): void
    {
        $this->connection         = $this->createMock(AdapterInterface::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->willReturn('cron_schedule');
        $this->check = new CronLastRunCheck($this->resourceConnection);
    }

    public function testOkWhenRecentlyRan(): void
    {
        $recentTime = date('Y-m-d H:i:s', time() - 60); // 1 minute ago
        $this->connection->method('fetchOne')->willReturn($recentTime);

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testFailWhenNeverRan(): void
    {
        $this->connection->method('fetchOne')->willReturn(null);

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }

    public function testFailWhenStale(): void
    {
        $staleTime = date('Y-m-d H:i:s', time() - 1800); // 30 minutes ago
        $this->connection->method('fetchOne')->willReturn($staleTime);

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }
}
