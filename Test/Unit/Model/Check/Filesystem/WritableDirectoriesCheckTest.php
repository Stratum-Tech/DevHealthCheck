<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Filesystem;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\Filesystem\WritableDirectoriesCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WritableDirectoriesCheckTest extends TestCase
{
    private DirectoryList|MockObject $directoryList;

    protected function setUp(): void
    {
        $this->directoryList = $this->createMock(DirectoryList::class);
    }

    public function testFailsWhenDirectoryNotWritable(): void
    {
        // Point to a non-existent root so all paths will be non-writable
        $this->directoryList->method('getRoot')->willReturn('/nonexistent/path');
        $this->directoryList->method('getPath')->willReturn('/nonexistent/path/pub');

        $check  = new WritableDirectoriesCheck($this->directoryList);
        $result = $check->run();

        $this->assertSame(StatusEnum::FAIL, $result->status);
    }

    public function testLabelAndSection(): void
    {
        $check = new WritableDirectoriesCheck($this->directoryList);
        $this->assertSame('Writable Directories', $check->getLabel());
        $this->assertSame('Filesystem', $check->getSection());
    }
}
