<?php

declare(strict_types=1);

namespace VM\RequestLogger\Interfaces;

use Error;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface LogMessageFormatter
{
    public function logError(Error $e): void;
    public function logException(Exception $e): void;
    public function logRequest(Request $body): void;
    public function logResponse(Response $response): void;
}
