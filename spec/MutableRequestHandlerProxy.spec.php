<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\NoResponseException;
use Quanta\Http\MutableRequestHandlerProxy;

describe('MutableRequestHandlerProxy', function () {

    beforeEach(function () {
        $this->handler = new MutableRequestHandlerProxy;
    });

    it('should implements RequestHandlerInterface', function () {

        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        context('when a request handler is set', function () {

            it('should return the response produced by the request handler', function () {

                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);
                $handler = mock(RequestHandlerInterface::class);

                $handler->handle->with($request)->returns($response);

                $this->handler->setRequestHandler($handler->get());

                $test = $this->handler->handle($request->get());

                expect($test)->toBe($response->get());

            });

        });

        context('when no request handler is set', function () {

            it('should throw an exception', function () {

                $request = mock(ServerRequestInterface::class);

                $test = fn () => $this->handler->handle($request->get());

                expect($test)->toThrow(new Exception('No response to return'));

            });

        });

    });

});
