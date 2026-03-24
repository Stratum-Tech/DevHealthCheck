<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Database;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class DatabaseVersionCheck implements CheckInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'DB Version';
    }

    public function getSection(): string
    {
        return 'Database';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();
        $version    = (string) $connection->fetchOne('SELECT VERSION()');

        $isMariaDb = stripos($version, 'mariadb') !== false;

        if ($isMariaDb) {
            // MariaDB is supported; just report it as info
            return CheckResult::info(sprintf('MariaDB %s', $version));
        }

        // MySQL: warn below 8.0
        preg_match('/^(\d+)\.(\d+)/', $version, $m);
        $major = (int) ($m[1] ?? 0);
        $minor = (int) ($m[2] ?? 0);

        if ($major < 8) {
            return CheckResult::warn(
                sprintf('MySQL %s', $version),
                'MySQL 8.0+ recommended for Magento 2.4.x',
            );
        }

        return CheckResult::ok(sprintf('MySQL %s', $version));
    }
}
