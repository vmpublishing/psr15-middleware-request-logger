<?php

declare(strict_types=1);

namespace VM\RequestLogger\Services;

use Error;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface as Body;
use Psr\Http\Message\UriInterface as Uri;
use Psr\Log\LoggerInterface;
use Throwable;
use VM\RequestLogger\Interfaces\LogMessageFormatter;

class StandardLogFormatter implements LogMessageFormatter
{
    public const MESSAGE_ERROR = 1;
    public const MESSAGE_ERROR_TRACE = 2;
    public const MESSAGE_EXCEPTION = 4;
    public const MESSAGE_EXCEPTION_TRACE = 8;
    public const MESSAGE_REQUEST_URI = 16;
    public const MESSAGE_REQUEST_BODY = 32;
    public const MESSAGE_REQUEST_HEADERS = 64;
    public const MESSAGE_RESPONSE_CODE = 128;
    public const MESSAGE_RESPONSE_BODY = 256;
    public const MESSAGE_RESPONSE_HEADERS = 512;

    private $logger;
    private $logLevel;
    private $logLevelMappings;

    public function __construct(
        LoggerInterface $logger,
        ?LogLevel $logLevel = null,
        $logLevelMappings = []
    ) {
        $this->logger = $logger;
        $this->logLevel = $logLevel ?? new LogLevel(LogLevel::LEVEL_NOTICE);

        $defaultLogLevelMappings = [
            self::MESSAGE_ERROR => new LogLevel(LogLevel::LEVEL_CRITICAL),
            self::MESSAGE_ERROR_TRACE => new LogLevel(LogLevel::LEVEL_ERROR),
            self::MESSAGE_EXCEPTION => new LogLevel(LogLevel::LEVEL_CRITICAL),
            self::MESSAGE_EXCEPTION_TRACE => new LogLevel(LogLevel::LEVEL_ERROR),
            self::MESSAGE_REQUEST_URI => new LogLevel(LogLevel::LEVEL_NOTICE),
            self::MESSAGE_REQUEST_BODY => new LogLevel(LogLevel::LEVEL_DEBUG),
            self::MESSAGE_REQUEST_HEADERS => new LogLevel(LogLevel::LEVEL_INFO),
            self::MESSAGE_RESPONSE_CODE => new LogLevel(LogLevel::LEVEL_NOTICE),
            self::MESSAGE_RESPONSE_BODY => new LogLevel(LogLevel::LEVEL_DEBUG),
            self::MESSAGE_RESPONSE_HEADERS => new LogLevel(LogLevel::LEVEL_INFO),
        ];
        $this->logLevelMappings = array_replace_recursive($defaultLogLevelMappings, $logLevelMappings);
    }

    public function logError(Error $e): void
    {
        $this->logErrorMessage($e, self::MESSAGE_ERROR);
        $this->logTrace($e, self::MESSAGE_ERROR_TRACE);
    }

    public function logException(Exception $e): void
    {
        $this->logErrorMessage($e, self::MESSAGE_EXCEPTION);
        $this->logTrace($e, self::MESSAGE_EXCEPTION_TRACE);
    }

    public function logRequest(Request $request): void
    {
        $this->logRequestUri($request->getUri());
        $this->logRequestHeaders($request->getHeaders());
        $this->logRequestBody($request->getBody());
    }

    public function logResponse(Response $response): void
    {
        $this->logResponseCode($response->getStatusCode());
        $this->logResponseHeaders($response->getHeaders());
        $this->logResponseBody($response->getBody());
    }

    private function logRequestBody(Body $body): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[self::MESSAGE_REQUEST_BODY])) {
            $this->logAs(self::MESSAGE_REQUEST_BODY, "request-body: {$body->__toString()}");
        }
    }

    private function logRequestHeaders(array $headers): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[self::MESSAGE_REQUEST_HEADERS])) {
            $this->logAs(self::MESSAGE_REQUEST_HEADERS, "request-headers: {$this->escapeArray($headers)}");
        }
    }

    private function logRequestUri(Uri $uri): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[self::MESSAGE_REQUEST_URI])) {
            $host = $uri->getHost();
            $path = $uri->getPath();
            $queryString = $uri->getQuery();
            if (!empty($queryString)) {
                $path .= '?' . $uri->getQuery();
            }
            $time = (new \DateTime())->format(\DateTime::ISO8601);
            $this->logAs(self::MESSAGE_REQUEST_URI, "[{$time}][{$host}]: {$path}");
        }
    }

    private function logResponseCode(int $code): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[self::MESSAGE_RESPONSE_CODE])) {
            $this->logAs(self::MESSAGE_RESPONSE_CODE, "response-code: {$code}");
        }
    }

    private function logResponseBody(Body $body): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[self::MESSAGE_RESPONSE_BODY])) {
            $this->logAs(self::MESSAGE_RESPONSE_BODY, "response-body: {$body->__toString()}");
        }
    }

    private function logResponseHeaders(array $headers): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[self::MESSAGE_RESPONSE_HEADERS])) {
            $this->logAs(self::MESSAGE_RESPONSE_HEADERS, "response-headers: {$this->escapeArray($headers)}");
        }
    }

    private function logErrorMessage(Throwable $e, int $type): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[$type])) {
            $this->logAs(self::MESSAGE_ERROR, "RequestLogger caught " . get_class($e) . ": {$e->getMessage()}");
        }
    }

    private function logTrace(Throwable $e, int $type): void
    {
        if ($this->logLevel->shouldLogFor($this->logLevelMappings[$type])) {
            $this->logAs(self::MESSAGE_ERROR_TRACE, "trace:\n" . $e->getTraceAsString());
        }
    }

    private function logAs(int $logType, string $contents): void
    {
        $logHandler = $this->logLevelMappings[$logType]->toString();
        $this->logger->$logHandler($contents);
    }

    private function escapeArray(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
