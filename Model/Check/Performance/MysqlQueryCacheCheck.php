<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Performance;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class MysqlQueryCacheCheck implements CheckInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'MySQL Query Cache';
    }

    public function getSection(): string
    {
        return 'Performance';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();

        // Determine MySQL major version first
        $version = (string) $connection->fetchOne('SELECT VERSION()');
        preg_match('/^(\d+)/', $version, $m);
        $major = (int) ($m[1] ?? 0);

        // query_cache was removed in MySQL 8.0
        if ($major >= 8 && stripos($version, 'mariadb') === false) {
            // On MySQL 8 query_cache variables don't exist — nothing to check
            return CheckResult::ok('query cache removed in MySQL 8 (no action needed)');
        }

        $row = $connection->fetchRow("SHOW VARIABLES LIKE 'query_cache_type'");
        $type = strtoupper((string) ($row['Value'] ?? 'OFF'));

        if ($type !== 'OFF' && $type !== '0') {
            return CheckResult::warn(
                sprintf('query_cache_type = %s', $type),
                'Query cache causes mutex contention under load — set query_cache_type=0 in MySQL config',
            );
        }

        return CheckResult::ok(sprintf('query_cache_type = %s', $type));
    }
}
