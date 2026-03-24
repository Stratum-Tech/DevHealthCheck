<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class SearchEngineConnectivityCheck implements CheckInterface
{
    private const TIMEOUT_SECONDS = 2;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Search Connectivity';
    }

    public function getSection(): string
    {
        return 'Search';
    }

    public function run(): CheckResult
    {
        $engine = (string) $this->scopeConfig->getValue('catalog/search/engine');

        if (empty($engine) || $engine === 'mysql') {
            return CheckResult::skip('not applicable for MySQL search engine');
        }

        $host = (string) ($this->scopeConfig->getValue('catalog/search/server_hostname') ?? '127.0.0.1');
        $port = (int)    ($this->scopeConfig->getValue('catalog/search/server_port') ?? 9200);

        // Strip scheme if present (e.g. "http://localhost")
        $host = preg_replace('#^https?://#', '', $host);

        $context = stream_context_create([
            'http' => ['timeout' => self::TIMEOUT_SECONDS],
            'ssl'  => ['verify_peer' => false],
        ]);

        $url      = sprintf('http://%s:%d', $host, $port);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return CheckResult::fail(
                sprintf('cannot connect to %s:%d', $host, $port),
                sprintf('Engine: %s — check server_hostname and server_port config', $engine),
            );
        }

        return CheckResult::ok(sprintf('connected to %s:%d (%s)', $host, $port, $engine));
    }
}
