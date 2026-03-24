<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Environment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class BaseUrlCheck implements CheckInterface
{
    private const LOCAL_PATTERNS = ['localhost', '127.0.0.1', '0.0.0.0', '.local', '.test', '.dev'];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'Base URL';
    }

    public function getSection(): string
    {
        return 'Environment';
    }

    public function run(): CheckResult
    {
        $baseUrl = (string) $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE,
        );

        if (empty($baseUrl)) {
            return CheckResult::fail('web/unsecure/base_url is not configured');
        }

        if ($this->appState->getMode() === State::MODE_PRODUCTION) {
            foreach (self::LOCAL_PATTERNS as $pattern) {
                if (str_contains($baseUrl, $pattern)) {
                    return CheckResult::warn(
                        $baseUrl,
                        'Base URL contains a local/development hostname in production mode',
                    );
                }
            }
        }

        return CheckResult::ok($baseUrl);
    }
}
