<?php

declare(strict_types=1);

namespace VM\RequestLogger\Tests\Traits;

use Psr\Log\LoggerInterface;

trait Logger
{
    private $logger;

    private function buildLogger(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'debug',
                'log',
                'emergency',
                'info',
                'warning',
                'alert',
                'critical',
                'error',
                'notice',
            ])
            ->getMock()
        ;
    }

    private function destroyLogger(): void
    {
        $this->logger = null;
    }
}
