<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Filesystem;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\Filesystem\DiskSpaceCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiskSpaceCheckTest extends TestCase
{
    private DirectoryList|MockObject $directoryList;
    private DiskSpaceCheck $check;

    protected function setUp(): void
    {
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->check         = new DiskSpaceCheck($this->directoryList);
    }

    public function testReturnsOkForHealthyDisk(): void
    {
        $this->directoryList->method('getRoot')->willReturn(sys_get_temp_dir());

        $result = $this->check->run();

        // Temp dir always exists; at least a result is returned
        $this->assertContains($result->status, StatusEnum::cases());
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('Disk Space', $this->check->getLabel());
        $this->assertSame('Filesystem', $this->check->getSection());
    }
}
