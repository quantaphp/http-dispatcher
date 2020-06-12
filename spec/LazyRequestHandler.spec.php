<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\LazyErrorStr;
use Quanta\Http\LazyRequestHandler;

describe('LazyRequestHandler', function () {

    beforeEach(function () {
        $this->factory = stub();

        $this->handler = new LazyRequestHandler($this->factory);
    });

    it('should implements RequestHandlerInterface', function () {
        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);
    });

    describe('->handle()', function () {

        context('when the value returned by the factory implements RequestHandlerInterface', function () {

            it('should proxy the returned request handler', function () {
                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);
                $handler = mock(RequestHandlerInterface::class);

                $handler->handle->with($request)->returns($response);

                $this->factory->returns($handler);

                $test = $this->handler->handle($request->get());

                expect($test)->toBe($response->get());
            });

        });

        context('when the value returned by the factory does not implement RequestHandlerInterface', function () {

            it('should throw an UnexpectedValueException', function () {
                $request = mock(ServerRequestInterface::class);

                $value = new class {};

                $this->factory->returns($value);

                $test = fn () => $this->handler->handle($request->get());

                expect($test)->toThrow(new UnexpectedValueException((string) new LazyErrorStr(
                    RequestHandlerInterface::class,
                    $value,
                )));
            });

        });

    });

});
