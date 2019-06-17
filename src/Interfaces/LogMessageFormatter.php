<?php

declare(strict_types=1);

namespace VM\RequestLogger\Interfaces;

use Error;
use Exception;
use Psr\Http\Message\UriInterface as Uri;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamInterface as Body;


interface LogMessageFormatter
{
    public function logError(Error $e): void;
    public function logException(Exception $e): void;
    public function logRequest(Request $body): void;
    public function logResponse(Response $response): void;
}
