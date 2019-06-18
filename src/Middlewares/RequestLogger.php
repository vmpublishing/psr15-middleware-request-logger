<?php

declare(strict_types=1);

namespace VM\RequestLogger\Middlewares;

use Error;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use VM\RequestLogger\Interfaces\LogMessageFormatter;

class RequestLogger implements MiddlewareInterface
{
    private $formatter;

    public function __construct(LogMessageFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function process(Request $request, RequestHandler $requestHandler): Response
    {
        $this->formatter->logRequest($request);
        try {
            $response = $requestHandler->handle($request);
            $this->formatter->logResponse($response);
            return $response;
        } catch (Error $e) {
            $this->formatter->logError($e);
            throw $e;
        } catch (Exception $e) {
            $this->formatter->logException($e);
            throw $e;
        }
    }
}
