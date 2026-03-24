<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Output;

use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use Stratum\DevHealthCheck\Model\Result\SectionResult;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleRenderer
{
    /**
     * @param SectionResult[] $sectionResults
     */
    public function render(OutputInterface $output, array $sectionResults): void
    {
        $this->renderHeader($output);

        $counts = [
            StatusEnum::OK->value   => 0,
            StatusEnum::WARN->value => 0,
            StatusEnum::FAIL->value => 0,
            StatusEnum::INFO->value => 0,
            StatusEnum::SKIP->value => 0,
        ];

        foreach ($sectionResults as $section) {
            $output->writeln('');
            $output->writeln(sprintf('<options=bold>[%s]</>', $section->sectionName));

            foreach ($section->results as $label => $result) {
                $counts[$result->status->value]++;
                $detail = $output->isVerbose() && $result->detail !== null
                    ? sprintf(' <fg=gray>(%s)</>', $result->detail)
                    : '';
                $output->writeln(sprintf(
                    '  %s  %-35s %s%s',
                    $result->status->label(),
                    $label,
                    $result->message,
                    $detail,
                ));
            }
        }

        $this->renderSummary($output, $counts);
    }

    /**
     * @param SectionResult[] $sectionResults
     */
    public function toJson(array $sectionResults): string
    {
        $sections = [];
        $counts = [
            'ok' => 0, 'warn' => 0, 'fail' => 0, 'info' => 0, 'skip' => 0,
        ];

        foreach ($sectionResults as $section) {
            $items = [];
            foreach ($section->results as $label => $result) {
                $key = strtolower($result->status->value);
                $counts[$key]++;
                $items[] = [
                    'label'   => $label,
                    'status'  => $result->status->value,
                    'message' => $result->message,
                    'detail'  => $result->detail,
                ];
            }
            $sections[$section->sectionName] = $items;
        }

        return json_encode(['sections' => $sections, 'summary' => $counts], JSON_PRETTY_PRINT);
    }

    private function renderHeader(OutputInterface $output): void
    {
        $title = ' Magento Environment Health Check ';
        $width = strlen($title) + 2;
        $bar   = str_repeat('═', $width);

        $output->writeln('');
        $output->writeln(sprintf('<options=bold>╔%s╗</>', $bar));
        $output->writeln(sprintf('<options=bold>║ %s ║</>', $title));
        $output->writeln(sprintf('<options=bold>╚%s╝</>', $bar));
    }

    private function renderSummary(OutputInterface $output, array $counts): void
    {
        $line = str_repeat('─', 44);
        $output->writeln('');
        $output->writeln($line);
        $output->writeln(sprintf(
            '  <options=bold>Summary:</>  <fg=green>%d OK</>  |  <fg=yellow>%d WARN</>  |  <fg=red>%d FAIL</>  |  <fg=cyan>%d INFO</>  |  <fg=gray>%d SKIP</>',
            $counts[StatusEnum::OK->value],
            $counts[StatusEnum::WARN->value],
            $counts[StatusEnum::FAIL->value],
            $counts[StatusEnum::INFO->value],
            $counts[StatusEnum::SKIP->value],
        ));
        $output->writeln($line);
    }
}
