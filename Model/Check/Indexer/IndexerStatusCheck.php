<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Indexer;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class IndexerStatusCheck implements CheckInterface
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
    ) {}

    public function getLabel(): string
    {
        return 'Indexer Status';
    }

    public function getSection(): string
    {
        return 'Indexers';
    }

    public function run(): CheckResult
    {
        $collection = $this->collectionFactory->create();
        $invalid    = [];

        foreach ($collection->getItems() as $indexer) {
            if ($indexer->isInvalid()) {
                $invalid[] = $indexer->getId();
            }
        }

        if ($invalid !== []) {
            return CheckResult::fail(
                sprintf('%d indexer(s) invalid', count($invalid)),
                implode(', ', $invalid),
            );
        }

        return CheckResult::ok(sprintf('all %d indexers valid', count($collection->getItems())));
    }
}
