<?php

declare(strict_types=1);

namespace VM\RequestLogger\Services;

class LogLevel
{
    public const LEVEL_DEBUG = 1;
    public const LEVEL_INFO = 2;
    public const LEVEL_NOTICE = 4;
    public const LEVEL_WARNING = 8;
    public const LEVEL_ERROR = 16;
    public const LEVEL_CRITICAL = 32;
    public const LEVEL_ALERT = 64;
    public const LEVEL_EMERGENCY = 128;

    private $mappings = [
        self::LEVEL_DEBUG => 'debug',
        self::LEVEL_INFO => 'info',
        self::LEVEL_NOTICE => 'notice',
        self::LEVEL_WARNING => 'warning',
        self::LEVEL_ERROR => 'error',
        self::LEVEL_CRITICAL => 'critical',
        self::LEVEL_ALERT => 'alert',
        self::LEVEL_EMERGENCY => 'emergency',
    ];

    private $level;

    public function __construct(int $level)
    {
        $this->level = $level;
    }

    public function shouldLogFor(LogLevel $right): bool
    {
        return $this->level <= $right->level;
    }

    public function toString(): string
    {
        return $this->mappings[$this->level];
    }
}
