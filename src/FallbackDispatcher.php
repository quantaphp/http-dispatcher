<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FallbackDispatcher extends AbstractDispatcher
{
    /**
     * @var \Psr\Http\Server\RequestHandlerInterface|null
     */
    private ?RequestHandlerInterface $next = null;

    /**
     * @inheritdoc
     */
    protected function setNextRequestHandler(RequestHandlerInterface $next): void
    {
        $this->next = $next;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (is_null($this->next)) {
            throw new NoResponseException;
        }

        return $this->next->handle($request);
    }
}
