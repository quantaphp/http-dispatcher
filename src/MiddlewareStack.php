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
        return (new LIFODispatcher($handler, ...$this->middleware))->handle($request);
    }
}
