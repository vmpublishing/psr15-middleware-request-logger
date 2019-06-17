<?php

declare(strict_types=1);

namespace VM\RequestLogger\Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use VM\Psr15Mocks\Middleware;
use VM\RequestLogger\Interfaces\LogMessageFormatter;
use VM\RequestLogger\Middlewares\RequestLogger;
use TypeError;
use RuntimeException;
use Error;
use Exception;

class RequestLoggerTest extends TestCase
{
    use Middleware;

    public function setUp(): void
    {
        $this->buildRequest();
        $this->buildResponse();
        $this->buildRequestHandler();
        $this->buildFormatter();
    }

    public function tearDown(): void
    {
        $this->destroyFormatter();
        $this->destroyRequestHandler();
        $this->destroyResponse();
        $this->destroyRequest();
    }

    public function testProcessShouldLogANormalResponse(): void
    {
        $this->formatter->expects($this->once())->method('logRequest')->with($this->request);
        $this->formatter->expects($this->once())->method('logResponse')->with($this->response);
        $this->formatter->expects($this->never())->method('logError');
        $this->formatter->expects($this->never())->method('logException');
        $this->requestHandler->expects($this->once())->method('handle')->with($this->request)->willReturn($this->response);

        $middleware = new RequestLogger($this->formatter);
        $middleware->process($this->request, $this->requestHandler);
    }

    public function testProcessShouldLogErrorsAndRethrow(): void
    {
        $throwable = new TypeError('wheee!');
        $this->formatter->expects($this->once())->method('logRequest')->with($this->request);
        $this->formatter->expects($this->once())->method('logError')->with($throwable);
        $this->formatter->expects($this->never())->method('logResponse');
        $this->formatter->expects($this->never())->method('logException');
        $this->requestHandler->expects($this->once())->method('handle')->with($this->request)->willThrowException($throwable);

        $middleware = new RequestLogger($this->formatter);
        $hasException = false;
        try {
            $middleware->process($this->request, $this->requestHandler);
        } catch(TypeError $e) {
            $hasException = true;
        }
        $this->assertTrue($hasException, "Exception was not rethrown");
    }

    public function testProcessShouldLogExceptionsAndRethrow(): void
    {
        $throwable = new RuntimeException('yaarrrr!');
        $this->formatter->expects($this->once())->method('logRequest')->with($this->request);
        $this->formatter->expects($this->once())->method('logException')->with($throwable);
        $this->formatter->expects($this->never())->method('logResponse');
        $this->formatter->expects($this->never())->method('logError');
        $this->requestHandler->expects($this->once())->method('handle')->with($this->request)->willThrowException($throwable);

        $middleware = new RequestLogger($this->formatter);
        $hasException = false;
        try {
            $middleware->process($this->request, $this->requestHandler);
        } catch(RuntimeException $e) {
            $hasException = true;
        }
        $this->assertTrue($hasException, "Exception was not rethrown");
    }

    private function buildFormatter(): void
    {
        $this->formatter = $this->getMockBuilder(LogMessageFormatter::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'logRequest',
                'logResponse',
                'logError',
                'logException',
            ])
            ->getMock()
        ;
    }

    private function destroyFormatter(): void
    {
        $this->formatter = null;
    }
}
