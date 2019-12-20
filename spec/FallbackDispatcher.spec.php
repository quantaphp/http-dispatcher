<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\AbstractDispatcher;
use Quanta\Http\FallbackDispatcher;
use Quanta\Http\NoResponseException;

describe('FallbackDispatcher', function () {

    beforeEach(function () {
        $this->dispatcher = new FallbackDispatcher;
    });

    it('should extends AbstractDispatcher', function () {

        expect($this->dispatcher)->toBeAnInstanceOf(AbstractDispatcher::class);

    });

    it('should implements MiddlewareInterface', function () {

        expect($this->dispatcher)->toBeAnInstanceOf(MiddlewareInterface::class);

    });

    it('should implements RequestHandlerInterface', function () {

        expect($this->dispatcher)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->process()', function () {

        it('should proxy the given request handler', function () {

            $request = mock(ServerRequestInterface::class);
            $response = mock(ResponseInterface::class);

            $handler = mock(RequestHandlerInterface::class);

            $handler->handle->with($request)->returns($response);

            $test = $this->dispatcher->process($request->get(), $handler->get());

            expect($test)->toBe($response->get());

        });

    });

    describe('->handle()', function () {

        it('should throw a NoResponseException', function () {

            $request = mock(ServerRequestInterface::class);

            $test = fn () => $this->dispatcher->handle($request->get());

            expect($test)->toThrow(new NoResponseException);

        });

    });

});
