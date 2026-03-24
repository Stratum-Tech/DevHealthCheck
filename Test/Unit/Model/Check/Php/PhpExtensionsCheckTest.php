<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Unit\Model\Check\Php;

use Stratum\DevHealthCheck\Model\Check\Php\PhpExtensionsCheck;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

class PhpExtensionsCheckTest extends TestCase
{
    private PhpExtensionsCheck $check;

    protected function setUp(): void
    {
        $this->check = new PhpExtensionsCheck();
    }

    public function testRunReturnsAResult(): void
    {
        $result = $this->check->run();

        $this->assertContains($result->status, StatusEnum::cases());
    }

    public function testLabelAndSection(): void
    {
        $this->assertSame('PHP Extensions', $this->check->getLabel());
        $this->assertSame('PHP', $this->check->getSection());
    }
}
