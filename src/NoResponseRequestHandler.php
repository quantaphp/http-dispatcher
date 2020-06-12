<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NoResponseRequestHandler implements RequestHandlerInterface
{
    /**
     * @var string
     */
    const MESSAGE = 'no response to return';

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw  new \Exception(self::MESSAGE);
    }
}
