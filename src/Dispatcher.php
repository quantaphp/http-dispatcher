<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Dispatcher extends AbstractDispatcher
{
    /**
     * @var \Quanta\Http\AbstractDispatcher
     */
    private AbstractDispatcher $handler;

    /**
     * @var \Psr\Http\Server\MiddlewareInterface
     */
    private MiddlewareInterface $middleware;

    /**
     * Return a new dispatcher dispatching the given middleware in LIFO order.
     *
     * @param \Psr\Http\Server\MiddlewareInterface ...$middleware
     * @return \Quanta\Http\AbstractDispatcher
     */
    public static function stack(MiddlewareInterface ...$middleware): AbstractDispatcher
    {
        return ($head = array_pop($middleware) ?? false)
            ? new self(self::stack(...$middleware), $head)
            : new FallbackDispatcher;
    }

    /**
     * Return a new dispatcher dispatching the given middleware in FIFO order.
     *
     * @param \Psr\Http\Server\MiddlewareInterface ...$middleware
     * @return \Quanta\Http\AbstractDispatcher
     */
    public static function queue(MiddlewareInterface ...$middleware): AbstractDispatcher
    {
        return ($head = array_shift($middleware) ?? false)
            ? new self(self::queue(...$middleware), $head)
            : new FallbackDispatcher;
    }

    /**
     * @param \Quanta\Http\AbstractDispatcher       $handler
     * @param \Psr\Http\Server\MiddlewareInterface  $middleware
     */
    public function __construct(AbstractDispatcher $handler, MiddlewareInterface $middleware)
    {
        $this->handler = $handler;
        $this->middleware = $middleware;
    }

    /**
     * @inheritdoc
     */
    protected function setNextRequestHandler(RequestHandlerInterface $next): void
    {
        $this->handler->setNextRequestHandler($next);
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // the given request handler is passed down to the fallback dispatcher.
        $this->handler->setNextRequestHandler($handler);

        return $this->middleware->process($request, $this->handler);
    }

   /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->handler);
    }
}
