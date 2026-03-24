<?php
declare(strict_types=1);

namespace Stratum\DevHealthCheck\Model\Enum;

enum StatusEnum: string
{
    case OK   = 'OK';
    case WARN = 'WARN';
    case FAIL = 'FAIL';
    case INFO = 'INFO';
    case SKIP = 'SKIP';

    public function label(): string
    {
        return match($this) {
            self::OK   => '<fg=green>  ✓  </>',
            self::WARN => '<fg=yellow>  ⚠  </>',
            self::FAIL => '<fg=red>  ✗  </>',
            self::INFO => '<fg=cyan>  ℹ  </>',
            self::SKIP => '<fg=gray>  –  </>',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OK   => 'green',
            self::WARN => 'yellow',
            self::FAIL => 'red',
            self::INFO => 'cyan',
            self::SKIP => 'gray',
        };
    }
}
