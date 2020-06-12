<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\LazyErrorStr;
use Quanta\Http\LazyMiddleware;

describe('LazyMiddleware', function () {

    beforeEach(function () {
        $this->factory = stub();

        $this->middleware = new LazyMiddleware($this->factory);
    });

    it('should implements MiddlewareInterface', function () {
        expect($this->middleware)->toBeAnInstanceOf(MiddlewareInterface::class);
    });

    describe('->handle()', function () {

        context('when the value returned by the factory implements MiddlewareInterface', function () {

            it('should proxy the returned middleware', function () {
                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);
                $middleware = mock(MiddlewareInterface::class);
                $handler = mock(RequestHandlerInterface::class);

                $middleware->process->with($request, $handler)->returns($response);

                $this->factory->returns($middleware);

                $test = $this->middleware->process($request->get(), $handler->get());

                expect($test)->toBe($response->get());
            });

        });

        context('when the value returned by the factory does not implement MiddlewareInterface', function () {

            it('should throw an UnexpectedValueException', function () {
                $request = mock(ServerRequestInterface::class);
                $handler = mock(RequestHandlerInterface::class);

                $value = new class {};

                $this->factory->returns($value);

                $test = fn () => $this->middleware->process($request->get(), $handler->get());

                expect($test)->toThrow(new UnexpectedValueException((string) new LazyErrorStr(
                    MiddlewareInterface::class,
                    $value,
                )));
            });

        });

    });

});
