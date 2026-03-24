<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Log;

readonly class LogEntry
{
    public function __construct(
        public string $file,
        public \DateTimeImmutable $timestamp,
        public string $level,
        public string $message,
        public ?string $exceptionClass,
    ) {}
}
