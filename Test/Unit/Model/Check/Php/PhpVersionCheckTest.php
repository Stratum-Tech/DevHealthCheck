<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Php;

use Stratum\DevHealthCheck\Model\Check\Php\PhpVersionCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

class PhpVersionCheckTest extends TestCase
{
    private PhpVersionCheck $check;

    protected function setUp(): void
    {
        $this->check = new PhpVersionCheck();
    }

    public function testRunReturnsAResult(): void
    {
        $result = $this->check->run();

        $this->assertContains($result->status, StatusEnum::cases());
        $this->assertNotEmpty($result->message);
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('PHP Version', $this->check->getLabel());
        $this->assertSame('PHP', $this->check->getSection());
    }
}
