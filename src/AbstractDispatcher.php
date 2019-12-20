<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractDispatcher implements MiddlewareInterface, RequestHandlerInterface
{
    abstract protected function setNextRequestHandler(RequestHandlerInterface $handler): void;
}
