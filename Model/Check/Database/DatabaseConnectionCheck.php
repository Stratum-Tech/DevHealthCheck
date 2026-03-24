<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Database;

use Magento\Framework\App\ResourceConnection;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class DatabaseConnectionCheck implements CheckInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function getLabel(): string
    {
        return 'DB Connection';
    }

    public function getSection(): string
    {
        return 'Database';
    }

    public function run(): CheckResult
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->query('SELECT 1');

        return CheckResult::ok('connected');
    }
}
