<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Console\Command;

use Stratum\DevHealthCheck\Model\Enum\StatusEnum;
use Stratum\DevHealthCheck\Model\HealthCheckRunner;
use Stratum\DevHealthCheck\Model\Output\ConsoleRenderer;
use Stratum\DevHealthCheck\Model\Score\ScoreCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCheckCommand extends Command
{
    public function __construct(
        private readonly HealthCheckRunner $runner,
        private readonly ConsoleRenderer $renderer,
        private readonly ScoreCalculator $scoreCalculator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('dev:healthcheck')
            ->setDescription('Run environment and deployment health checks')
            ->addOption('section',      's', InputOption::VALUE_OPTIONAL, 'Run only a specific section')
            ->addOption('format',       'f', InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->addOption('fail-on-warn', null, InputOption::VALUE_NONE,    'Exit 2 if any WARN results exist');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $section    = $input->getOption('section');
        $format     = $input->getOption('format');
        $failOnWarn = (bool) $input->getOption('fail-on-warn');

        $sectionResults = $this->runner->run($section);

        $scoreResult = $this->scoreCalculator->calculate($sectionResults);

        if ($format === 'json') {
            $output->writeln($this->renderer->toJson($sectionResults, $scoreResult));
            return $this->resolveExitCode($sectionResults, $failOnWarn);
        }

        $this->renderer->render($output, $sectionResults, $scoreResult);

        return $this->resolveExitCode($sectionResults, $failOnWarn);
    }

    private function resolveExitCode(array $sectionResults, bool $failOnWarn): int
    {
        $hasFailure = false;
        $hasWarning = false;

        foreach ($sectionResults as $section) {
            foreach ($section->results as $result) {
                if ($result->status === StatusEnum::FAIL) {
                    $hasFailure = true;
                }
                if ($result->status === StatusEnum::WARN) {
                    $hasWarning = true;
                }
            }
        }

        if ($hasFailure) {
            return Command::FAILURE;
        }

        if ($failOnWarn && $hasWarning) {
            return 2;
        }

        return Command::SUCCESS;
    }
}
