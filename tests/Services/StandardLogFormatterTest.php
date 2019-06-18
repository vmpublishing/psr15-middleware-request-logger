<?php

declare(strict_types=1);

namespace VM\RequestLogger\Tests\Services;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;
use VM\Psr15Mocks\Middleware;
use VM\RequestLogger\Services\LogLevel;
use VM\RequestLogger\Services\StandardLogFormatter;
use VM\RequestLogger\Tests\Traits\Logger;

class StandardLogFormatterTest extends TestCase
{
    use Logger;
    use Middleware;

    public function setUp(): void
    {
        $this->buildLogger();
        $this->buildRequest();
        $this->buildUri();
        $this->buildBody();
        $this->buildResponse();
    }

    public function tearDown(): void
    {
        $this->destroyResponse();
        $this->destroyBody();
        $this->destroyUri();
        $this->destroyRequest();
        $this->destroyLogger();
    }

    public function testLogErrorShouldLogErrorsWithDefaultMethods(): void
    {
        $this->logger->expects($this->once())->method('error');
        $this->logger->expects($this->once())->method('critical');

        $logLevel = new LogLevel(LogLevel::LEVEL_DEBUG);
        $service = new StandardLogFormatter($this->logger, $logLevel);
        $service->logError(new TypeError('foo!'));
    }

    public function testLogErrorShouldNotLogWhenTheLevelIsNotHighEnough(): void
    {
        $this->logger->expects($this->never())->method('error');
        $this->logger->expects($this->never())->method('critical');

        $logLevel = new LogLevel(LogLevel::LEVEL_EMERGENCY);
        $service = new StandardLogFormatter($this->logger, $logLevel);
        $service->logError(new TypeError('foo!'));
    }

    public function testLogLevelShouldBeConfigurable(): void
    {
        $this->logger->expects($this->once())->method('emergency');
        $this->logger->expects($this->never())->method('critical');

        $logLevel = new LogLevel(LogLevel::LEVEL_EMERGENCY);
        $service = new StandardLogFormatter($this->logger, $logLevel, [StandardLogFormatter::MESSAGE_ERROR => $logLevel]);
        $service->logError(new TypeError('foo!'));
    }

    public function testLogExceptionShouldLogExceptionsWithDefaultMethods(): void
    {
        $this->logger->expects($this->once())->method('error');
        $this->logger->expects($this->once())->method('critical');

        $logLevel = new LogLevel(LogLevel::LEVEL_DEBUG);
        $service = new StandardLogFormatter($this->logger, $logLevel);
        $service->logException(new RuntimeException('foo!'));
    }

    public function testLogRequestShouldLogAllRequestLogTypes(): void
    {
        $this->logger->expects($this->once())->method('notice');
        $this->logger->expects($this->once())->method('debug');
        $this->logger->expects($this->once())->method('info');

        $this->request->expects($this->once())->method('getUri')->willReturn($this->uri);
        $this->request->expects($this->once())->method('getHeaders')->willReturn(['X-SOME' => 'header']);
        $this->request->expects($this->once())->method('getBody')->willReturn($this->body);

        $this->uri->expects($this->once())->method('getHost')->willReturn('www.host.de');
        $this->uri->expects($this->once())->method('getPath')->willReturn('/some/path');
        $this->uri->expects($this->exactly(2))->method('getQuery')->willReturn('some=query');

        $this->body->expects($this->once())->method('__toString')->willReturn('some body content');

        $logLevel = new LogLevel(LogLevel::LEVEL_DEBUG);
        $service = new StandardLogFormatter($this->logger, $logLevel);
        $service->logRequest($this->request);
    }

    public function testLogResponseShouldLogAllResponseLogTypes(): void
    {
        $this->logger->expects($this->once())->method('notice');
        $this->logger->expects($this->once())->method('debug');
        $this->logger->expects($this->once())->method('info');

        $this->response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $this->response->expects($this->once())->method('getHeaders')->willReturn(['X-SOME' => 'header']);
        $this->response->expects($this->once())->method('getBody')->willReturn($this->body);

        $this->body->expects($this->once())->method('__toString')->willReturn('some response body content');

        $logLevel = new LogLevel(LogLevel::LEVEL_DEBUG);
        $service = new StandardLogFormatter($this->logger, $logLevel);
        $service->logResponse($this->response);
    }

    public function testMayInstantiateWithoutSecondParam(): void
    {
        $service = new StandardLogFormatter($this->logger);
        $this->assertSame(StandardLogFormatter::class, get_class($service));
    }
}
