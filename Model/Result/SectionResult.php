<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Result;

final readonly class SectionResult
{
    /**
     * @param CheckResult[] $results
     */
    public function __construct(
        public string $sectionName,
        public array $results,
    ) {}
}
