<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    private RequestHandlerInterface $handler;

    /**
     * @var \Psr\Http\Server\MiddlewareInterface
     */
    private MiddlewareInterface $middleware;

    /**
     * Return a new request handler with the given middleware in LIFO order.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface  $handler
     * @param \Psr\Http\Server\MiddlewareInterface      ...$middleware
     * @return \Psr\Http\Server\RequestHandlerInterface
     */
    public static function stack(RequestHandlerInterface $handler, MiddlewareInterface ...$middleware): RequestHandlerInterface
    {
        return ($head = array_pop($middleware) ?? false)
            ? new self(self::stack($handler, ...$middleware), $head)
            : $handler;
    }

    /**
     * Return a new request handler with the given middleware in FIFO order.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface  $handler
     * @param \Psr\Http\Server\MiddlewareInterface      ...$middleware
     * @return \Psr\Http\Server\RequestHandlerInterface
     */
    public static function queue(RequestHandlerInterface $handler, MiddlewareInterface ...$middleware): RequestHandlerInterface
    {
        return ($head = array_shift($middleware) ?? false)
            ? new self(self::queue($handler, ...$middleware), $head)
            : $handler;
    }

    /**
     * @param \Psr\Http\Server\RequestHandlerInterface  $handler
     * @param \Psr\Http\Server\MiddlewareInterface      $middleware
     */
    public function __construct(RequestHandlerInterface $handler, MiddlewareInterface $middleware)
    {
        $this->handler = $handler;
        $this->middleware = $middleware;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->handler);
    }
}
