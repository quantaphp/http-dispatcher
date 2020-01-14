<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Dispatcher implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    private RequestHandlerInterface $handler;

    /**
     * @var \Quanta\Http\MutableRequestHandlerProxy
     */
    private MutableRequestHandlerProxy $proxy;

    /**
     * Return a new dispatcher with the given middleware in LIFO order.
     *
     * @param \Psr\Http\Server\MiddlewareInterface ...$middleware
     * @return \Quanta\Http\Dispatcher
     */
    public static function stack(MiddlewareInterface ...$middleware): self
    {
        $proxy = new MutableRequestHandlerProxy;

        $handler = RequestHandler::stack($proxy, ...$middleware);

        return new self($handler, $proxy);
    }

    /**
     * Return a new dispatcher with the given middleware in FIFO order.
     *
     * @param \Psr\Http\Server\MiddlewareInterface ...$middleware
     * @return \Quanta\Http\Dispatcher
     */
    public static function queue(MiddlewareInterface ...$middleware): self
    {
        $proxy = new MutableRequestHandlerProxy;

        $handler = RequestHandler::queue($proxy, ...$middleware);

        return new self($handler, $proxy);
    }

    /**
     * @param \Psr\Http\Server\RequestHandlerInterface  $handler
     * @param \Quanta\Http\MutableRequestHandlerProxy   $proxy
     */
    private function __construct(RequesthandlerInterface $handler, MutableRequestHandlerProxy $proxy)
    {
        $this->handler = $handler;
        $this->proxy = $proxy;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->proxy->setRequesthandler($handler);

        return $this->handler->handle($request);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handler->handle($request);
    }
}
