<?php declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FIFODispatcher implements RequestHandlerInterface
{
    /**
     * The innermost request handler.
     *
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    private $handler;

    /**
     * The array of middleware processing the request/response in FIFO order.
     *
     * @var \Psr\Http\Server\MiddlewareInterface[]
     */
    private $middleware;

    /**
     * Constructor.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface  $handler
     * @param \Psr\Http\Server\MiddlewareInterface      ...$middleware
     */
    public function __construct(RequestHandlerInterface $handler, MiddlewareInterface ...$middleware)
    {
        $this->handler = $handler;
        $this->middleware = $middleware;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $remaining = count($this->middleware);

        if ($remaining == 0) {
            return $this->handler->handle($request);
        }

        if ($remaining == 1) {
            return $this->middleware[0]->process($request, $this->handler);
        }

        $middleware = array_slice($this->middleware, 1);
        $handler = new FIFODispatcher($this->handler, ...$middleware);

        return $this->middleware[0]->process($request, $handler);
    }
}
