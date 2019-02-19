<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\MiddlewareQueue;

require_once __DIR__ . '/.test/TestMiddleware.php';

describe('MiddlewareQueue', function () {

    context('when there is no middleware', function () {

        beforeEach(function () {

            $this->middleware = new MiddlewareQueue;

        });

        it('should implement MiddlewareInterface', function () {

            expect($this->middleware)->toBeAnInstanceOf(MiddlewareInterface::class);

        });

        describe('->process()', function () {

            it('should return the response produced by the given request handler', function () {

                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);

                $handler = mock(RequestHandlerInterface::class);

                $handler->handle->with($request)->returns($response);

                $test = $this->middleware->process($request->get(), $handler->get());

                expect($test)->toBe($response->get());

            });

        });

    });

    context('when there is at least one middleware', function () {

        beforeEach(function () {

            $this->middleware = new MiddlewareQueue(...[
                new TestMiddleware('m1'),
                new TestMiddleware('m2'),
                new TestMiddleware('m3'),
            ]);

        });

        it('should implement MiddlewareInterface', function () {

            expect($this->middleware)->toBeAnInstanceOf(MiddlewareInterface::class);

        });

        describe('->process()', function () {

            it('should process the request/response using the middleware in FIFO order', function () {

                $request1 = mock(ServerRequestInterface::class);
                $request2 = mock(ServerRequestInterface::class);
                $request3 = mock(ServerRequestInterface::class);
                $request4 = mock(ServerRequestInterface::class);

                $response1 = mock(ResponseInterface::class);
                $response2 = mock(ResponseInterface::class);
                $response3 = mock(ResponseInterface::class);
                $response4 = mock(ResponseInterface::class);

                $handler = mock(RequestHandlerInterface::class);

                $request1->withHeader->with('test', 'm1')->returns($request2);
                $request2->withHeader->with('test', 'm2')->returns($request3);
                $request3->withHeader->with('test', 'm3')->returns($request4);

                $response1->withHeader->with('test', 'm3')->returns($response2);
                $response2->withHeader->with('test', 'm2')->returns($response3);
                $response3->withHeader->with('test', 'm1')->returns($response4);

                $handler->handle->with($request4)->returns($response1);

                $test = $this->middleware->process($request1->get(), $handler->get());

                expect($test)->toBe($response4->get());

            });

        });

    });

});
