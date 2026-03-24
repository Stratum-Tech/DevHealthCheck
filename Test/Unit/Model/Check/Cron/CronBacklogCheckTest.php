<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Stratum\DevHealthCheck\Model\Check\Cron\CronBacklogCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronBacklogCheckTest extends TestCase
{
    private ResourceConnection|MockObject $resourceConnection;
    private AdapterInterface|MockObject $connection;
    private CronBacklogCheck $check;

    protected function setUp(): void
    {
        $this->connection         = $this->createMock(AdapterInterface::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->willReturn('cron_schedule');
        $this->check = new CronBacklogCheck($this->resourceConnection);
    }

    public function testOkWhenNoBacklog(): void
    {
        $this->connection->method('fetchOne')->willReturn('0');

        $this->assertSame(StatusEnum::OK, $this->check->run()->status);
    }

    public function testWarnWhenModerateBacklog(): void
    {
        $this->connection->method('fetchOne')->willReturn('15');

        $this->assertSame(StatusEnum::WARN, $this->check->run()->status);
    }

    public function testFailWhenCriticalBacklog(): void
    {
        $this->connection->method('fetchOne')->willReturn('55');

        $this->assertSame(StatusEnum::FAIL, $this->check->run()->status);
    }
}
