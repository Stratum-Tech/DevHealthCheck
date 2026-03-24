<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Environment;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class CryptKeyCheck implements CheckInterface
{
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Crypt Key';
    }

    public function getSection(): string
    {
        return 'Environment';
    }

    public function run(): CheckResult
    {
        $key = $this->deploymentConfig->get('crypt/key');

        if (empty($key)) {
            return CheckResult::fail('crypt/key not set in env.php');
        }

        return CheckResult::ok('present');
    }
}
