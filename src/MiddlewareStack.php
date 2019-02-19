<?php declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MiddlewareStack implements MiddlewareInterface
{
    /**
     * The array of middleware processing the request/response in LIFO order.
     *
     * @var \Psr\Http\Server\MiddlewareInterface[]
     */
    private $middleware;

    /**
     * Constructor.
     *
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
        if (count($this->middleware) == 0) {
            return $handler->handle($request);
        }

        $handler = new DispatchedMiddleware($handler, new MiddlewareStack(
            ...array_slice($this->middleware, 0, -1)
        ));

        return end($this->middleware)->process($request, $handler);
    }
}
