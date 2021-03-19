<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NoResponseRequestHandler implements RequestHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new \Exception('No response returned');
    }
}
