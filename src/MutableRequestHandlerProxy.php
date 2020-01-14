<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MutableRequestHandlerProxy implements RequestHandlerInterface
{
    /**
     * @var \Psr\Http\Server\RequestHandlerInterface|null
     */
    private ?RequestHandlerInterface $handler = null;

    /**
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return void
     */
    public function setRequestHandler(RequestHandlerInterface $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (is_null($this->handler)) {
            throw new \Exception('No response to return');
        }

        return $this->handler->handle($request);
    }
}
