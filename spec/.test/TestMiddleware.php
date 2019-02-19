<?php

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TestMiddleware implements MiddlewareInterface
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withHeader('test', $this->value);

        $response = $handler->handle($request);

        return $response->withHeader('test', $this->value);
    }
}
