<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Result;

use Stratum\DevHealthCheck\Model\Enum\StatusEnum;

final readonly class CheckResult
{
    public function __construct(
        public StatusEnum $status,
        public string $message,
        public ?string $detail = null,
    ) {}

    public static function ok(string $message, ?string $detail = null): self
    {
        return new self(StatusEnum::OK, $message, $detail);
    }

    public static function warn(string $message, ?string $detail = null): self
    {
        return new self(StatusEnum::WARN, $message, $detail);
    }

    public static function fail(string $message, ?string $detail = null): self
    {
        return new self(StatusEnum::FAIL, $message, $detail);
    }

    public static function info(string $message, ?string $detail = null): self
    {
        return new self(StatusEnum::INFO, $message, $detail);
    }

    public static function skip(string $message, ?string $detail = null): self
    {
        return new self(StatusEnum::SKIP, $message, $detail);
    }
}
