<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model;

use Stratum\DevHealthCheck\Model\Check\CheckInterface;
use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use Stratum\DevHealthCheck\Model\Result\CheckResult;
use Stratum\DevHealthCheck\Model\Result\SectionResult;

class HealthCheckRunner
{
    /**
     * @param CheckInterface[] $checks  Injected via di.xml array
     */
    public function __construct(
        private readonly array $checks = [],
    ) {}

    /**
     * @return SectionResult[]
     */
    public function run(?string $sectionFilter = null): array
    {
        $grouped = [];

        foreach ($this->checks as $check) {
            $section = $check->getSection();

            if ($sectionFilter !== null && strtolower($section) !== strtolower($sectionFilter)) {
                continue;
            }

            try {
                $result = $check->run();
            } catch (\Throwable $e) {
                $result = CheckResult::fail(
                    $check->getLabel(),
                    sprintf('Check threw exception: %s', $e->getMessage()),
                );
            }

            $grouped[$section][] = ['label' => $check->getLabel(), 'result' => $result];
        }

        $sections = [];
        foreach ($grouped as $sectionName => $items) {
            $results = array_column($items, 'result');
            $sections[] = new SectionResult($sectionName, array_combine(
                array_column($items, 'label'),
                $results,
            ));
        }

        return $sections;
    }
}
