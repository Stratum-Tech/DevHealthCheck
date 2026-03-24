<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Environment;

use Magento\Framework\App\DeploymentConfig;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class AdminUrlCheck implements CheckInterface
{
    private const OBVIOUS_DEFAULTS = ['admin', 'backend', 'admin123', 'administrator', 'adminpanel', 'manage', 'manager'];

    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Admin URL';
    }

    public function getSection(): string
    {
        return 'Environment';
    }

    public function run(): CheckResult
    {
        $frontName = (string) $this->deploymentConfig->get('backend/frontName');

        if (empty($frontName)) {
            return CheckResult::fail('backend/frontName not set in env.php');
        }

        if (in_array(strtolower($frontName), self::OBVIOUS_DEFAULTS, true)) {
            return CheckResult::warn(
                sprintf('/%s', $frontName),
                'Admin path is a well-known default — consider a custom path for security',
            );
        }

        return CheckResult::ok(sprintf('/%s', $frontName));
    }
}
