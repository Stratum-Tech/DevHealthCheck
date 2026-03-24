<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class SearchEngineConfigCheck implements CheckInterface
{
    private const RECOMMENDED_ENGINES = ['elasticsearch7', 'elasticsearch8', 'opensearch'];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Search Engine';
    }

    public function getSection(): string
    {
        return 'Search';
    }

    public function run(): CheckResult
    {
        $engine = (string) $this->scopeConfig->getValue('catalog/search/engine');

        if (empty($engine)) {
            return CheckResult::fail('catalog/search/engine is not configured');
        }

        if ($engine === 'mysql') {
            return CheckResult::warn(
                'MySQL (deprecated)',
                'MySQL search is deprecated — configure Elasticsearch or OpenSearch',
            );
        }

        if (in_array($engine, self::RECOMMENDED_ENGINES, true)) {
            return CheckResult::ok($engine);
        }

        return CheckResult::info(sprintf('%s (unrecognised engine)', $engine));
    }
}
