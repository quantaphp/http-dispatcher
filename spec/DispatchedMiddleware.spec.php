<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\DispatchedMiddleware;

describe('DispatchedMiddleware', function () {

    beforeEach(function () {

        $this->delegate = mock(RequestHandlerInterface::class);
        $this->middleware = mock(MiddlewareInterface::class);

        $this->handler = new DispatchedMiddleware($this->delegate->get(), $this->middleware->get());

    });

    it('should implement RequestHandlerInterface', function () {

        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        it('should return the response produced by the middleware with the given request', function () {

            $request = mock(ServerRequestInterface::class);
            $response = mock(ResponseInterface::class);

            $this->middleware->process->with($request, $this->delegate)->returns($response);

            $test = $this->handler->handle($request->get());

            expect($test)->toBe($response->get());

        });

    });

});
