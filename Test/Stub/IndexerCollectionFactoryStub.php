<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Test\Stub;

/**
 * Stub for the generated Magento\Indexer\Model\Indexer\CollectionFactory.
 * Used in unit tests when a full Magento installation is not available.
 */
class IndexerCollectionFactoryStub
{
    public function create(): \Magento\Indexer\Model\Indexer\Collection
    {
        throw new \LogicException('Stub not configured — mock this method in your test.');
    }
}
