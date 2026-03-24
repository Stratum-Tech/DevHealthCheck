<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Score;

use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use Stratum\DevHealthCheck\Model\Result\SectionResult;

class ScoreCalculator
{
    /**
     * Point budget per section. Checks within a section share the budget equally.
     * Sections not listed here default to 2 points each.
     */
    private const SECTION_WEIGHTS = [
        'Security'         => 20,
        'Database'         => 15,
        'Cache'            => 10,
        'Cron'             => 10,
        'Deploy & Mode'    => 8,
        'PHP'              => 8,
        'Environment'      => 7,
        'Filesystem'       => 7,
        'Indexers'         => 5,
        'Search'           => 5,
        'Infrastructure'   => 5,
        'Performance'      => 4,
        'Logging'          => 3,
        'Config Integrity' => 3,
        'Extensions'       => 3,
        'Storage'          => 2,
    ];

    private const DEFAULT_SECTION_WEIGHT = 2;

    private const WARN_MULTIPLIER = 0.5;

    /**
     * @param SectionResult[] $sectionResults
     */
    public function calculate(array $sectionResults): ScoreResult
    {
        $earnedPoints = 0.0;
        $totalPoints  = 0.0;

        foreach ($sectionResults as $section) {
            $sectionWeight    = self::SECTION_WEIGHTS[$section->sectionName] ?? self::DEFAULT_SECTION_WEIGHT;
            $checksInSection  = count($section->results);

            if ($checksInSection === 0) {
                continue;
            }

            $pointsPerCheck = $sectionWeight / $checksInSection;

            foreach ($section->results as $result) {
                // SKIP checks are excluded — they don't penalise irrelevant environments
                if ($result->status === StatusEnum::SKIP) {
                    continue;
                }

                $totalPoints += $pointsPerCheck;

                $earnedPoints += match($result->status) {
                    StatusEnum::OK, StatusEnum::INFO => $pointsPerCheck,
                    StatusEnum::WARN                 => $pointsPerCheck * self::WARN_MULTIPLIER,
                    StatusEnum::FAIL                 => 0.0,
                    default                          => $pointsPerCheck,
                };
            }
        }

        if ($totalPoints === 0.0) {
            return new ScoreResult(100, 'A', 0, 0);
        }

        $score = (int) round(($earnedPoints / $totalPoints) * 100);

        return new ScoreResult(
            score:        $score,
            grade:        $this->grade($score),
            earnedPoints: (int) round($earnedPoints),
            totalPoints:  (int) round($totalPoints),
        );
    }

    private function grade(int $score): string
    {
        return match(true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            $score >= 45 => 'D',
            default      => 'F',
        };
    }
}
