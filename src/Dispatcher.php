<?php

declare(strict_types=1);

namespace Quanta\Http;

use function Quanta\Http\queue;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Dispatcher implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var array<int, \Psr\Http\Server\MiddlewareInterface>
     */
    private array $middleware;

    /**
     * @param \Psr\Http\Server\MiddlewareInterface ...$middleware
     */
    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return queue($handler, ...$this->middleware)->handle($request);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return queue(new NoResponseRequestHandler, ...$this->middleware)->handle($request);
    }
}
