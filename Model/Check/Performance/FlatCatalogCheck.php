<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Performance;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class FlatCatalogCheck implements CheckInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function getLabel(): string
    {
        return 'Flat Catalog';
    }

    public function getSection(): string
    {
        return 'Performance';
    }

    public function run(): CheckResult
    {
        $flatProduct  = $this->scopeConfig->isSetFlag(
            'catalog/frontend/flat_catalog_product',
            ScopeInterface::SCOPE_STORE,
        );
        $flatCategory = $this->scopeConfig->isSetFlag(
            'catalog/frontend/flat_catalog_category',
            ScopeInterface::SCOPE_STORE,
        );

        $off = [];

        if (!$flatProduct) {
            $off[] = 'products';
        }
        if (!$flatCategory) {
            $off[] = 'categories';
        }

        if ($off !== []) {
            return CheckResult::warn(
                sprintf('flat tables disabled for: %s', implode(', ', $off)),
                'Enable flat catalog for improved frontend performance on large catalogs',
            );
        }

        return CheckResult::ok('flat tables enabled for products and categories');
    }
}
