<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\MutableRequestHandlerProxy;

describe('MutableRequestHandlerProxy', function () {

    beforeEach(function () {
        $this->delegate = mock(RequestHandlerInterface::class);

        $this->handler = new MutableRequestHandlerProxy(
            $this->delegate->get(),
        );
    });

    it('should implements RequestHandlerInterface', function () {
        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);
    });

    describe('->handle()', function () {

        context('when no request handler has been set', function () {

            it('should proxy the initial request handler', function () {
                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);

                $this->delegate->handle->with($request)->returns($response);

                $test = $this->handler->handle($request->get());

                expect($test)->toBe($response->get());
            });

        });

        context('when a request handler has been set', function () {

            it('should proxy the new request handler', function () {
                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);
                $delegate = mock(RequestHandlerInterface::class);

                $delegate->handle->with($request)->returns($response);

                $this->handler->setRequestHandler($delegate->get());

                $test = $this->handler->handle($request->get());

                expect($test)->toBe($response->get());
            });

        });

    });

});
