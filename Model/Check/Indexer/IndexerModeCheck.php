<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check\Indexer;

use Magento\Framework\App\State;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Result\CheckResult;

class IndexerModeCheck implements CheckInterface
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly State $appState,
    ) {}

    public function getLabel(): string
    {
        return 'Indexer Mode';
    }

    public function getSection(): string
    {
        return 'Indexers';
    }

    public function run(): CheckResult
    {
        if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
            return CheckResult::skip('mode check only relevant in production');
        }

        $collection = $this->collectionFactory->create();
        $realtime   = [];

        foreach ($collection->getItems() as $indexer) {
            if (!$indexer->isScheduled()) {
                $realtime[] = $indexer->getId();
            }
        }

        if ($realtime !== []) {
            return CheckResult::warn(
                sprintf('%d indexer(s) on realtime', count($realtime)),
                implode(', ', $realtime) . ' — consider switching to "Update by Schedule"',
            );
        }

        return CheckResult::ok('all indexers on schedule mode');
    }
}
