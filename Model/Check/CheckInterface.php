<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Check;

use Stratum\DevHealthCheck\Model\Result\CheckResult;

interface CheckInterface
{
    public function getLabel(): string;
    public function getSection(): string;
    public function run(): CheckResult;
}
