<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Score;

final readonly class ScoreResult
{
    public function __construct(
        public int $score,
        public string $grade,
        public int $earnedPoints,
        public int $totalPoints,
    ) {}
}
