<?php declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LIFODispatcher implements RequestHandlerInterface
{
    /**
     * The innermost request handler.
     *
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    private $handler;

    /**
     * The array of middleware processing the request/response in LIFO order.
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

        $middleware = array_slice($this->middleware, 0, -1);
        $handler = new LIFODispatcher($this->handler, ...$middleware);

        return $this->middleware[$remaining - 1]->process($request, $handler);
    }
}
