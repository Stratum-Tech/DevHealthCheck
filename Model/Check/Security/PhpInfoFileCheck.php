<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Security;

use Magento\Framework\Filesystem\DirectoryList;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class PhpInfoFileCheck implements CheckInterface
{
    private const DANGEROUS_FILES = [
        'info.php',
        'phpinfo.php',
        'php_info.php',
        'test.php',
        'phptest.php',
    ];

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {}

    public function getLabel(): string
    {
        return 'PHP Info Files';
    }

    public function getSection(): string
    {
        return 'Security';
    }

    public function run(): CheckResult
    {
        $pubDir = $this->directoryList->getPath('pub');
        $found  = [];

        foreach (self::DANGEROUS_FILES as $file) {
            if (file_exists($pubDir . '/' . $file)) {
                $found[] = $file;
            }
        }

        if ($found !== []) {
            return CheckResult::fail(
                sprintf('%d dangerous file(s) found in pub/', count($found)),
                implode(', ', $found) . ' — remove immediately',
            );
        }

        return CheckResult::ok('no phpinfo files found in pub/');
    }
}
