<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Filesystem;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\Filesystem\EnvPhpPermissionsCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EnvPhpPermissionsCheckTest extends TestCase
{
    private DirectoryList|MockObject $directoryList;
    private EnvPhpPermissionsCheck $check;

    protected function setUp(): void
    {
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->check         = new EnvPhpPermissionsCheck($this->directoryList);
    }

    public function testFailsWhenFileNotFound(): void
    {
        $this->directoryList->method('getRoot')->willReturn('/nonexistent/path');

        $result = $this->check->run();

        $this->assertSame(StatusEnum::FAIL, $result->status);
    }

    public function testWarnOnWorldReadableFile(): void
    {
        $tmpDir = sys_get_temp_dir() . '/osc_test_' . uniqid();
        mkdir($tmpDir . '/app/etc', 0755, true);
        $envFile = $tmpDir . '/app/etc/env.php';
        file_put_contents($envFile, '<?php return [];');
        chmod($envFile, 0644);

        $this->directoryList->method('getRoot')->willReturn($tmpDir);
        $result = (new EnvPhpPermissionsCheck($this->directoryList))->run();

        $this->assertSame(StatusEnum::WARN, $result->status);

        // Cleanup
        unlink($envFile);
        rmdir($tmpDir . '/app/etc');
        rmdir($tmpDir . '/app');
        rmdir($tmpDir);
    }

    public function testOkOnRestrictedFile(): void
    {
        $tmpDir = sys_get_temp_dir() . '/osc_test_' . uniqid();
        mkdir($tmpDir . '/app/etc', 0755, true);
        $envFile = $tmpDir . '/app/etc/env.php';
        file_put_contents($envFile, '<?php return [];');
        chmod($envFile, 0640); // not world-readable

        $this->directoryList->method('getRoot')->willReturn($tmpDir);
        $result = (new EnvPhpPermissionsCheck($this->directoryList))->run();

        $this->assertSame(StatusEnum::OK, $result->status);

        // Cleanup
        unlink($envFile);
        rmdir($tmpDir . '/app/etc');
        rmdir($tmpDir . '/app');
        rmdir($tmpDir);
    }
}
