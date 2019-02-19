<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\LIFODispatcher;

require_once __DIR__ . '/.test/TestMiddleware.php';

describe('LIFODispatcher', function () {

    context('when there is no middleware', function () {

        beforeEach(function () {

            $this->handler = mock(RequestHandlerInterface::class);

            $this->dispatcher = new LIFODispatcher($this->handler->get());

        });

        it('should implement RequestHandlerInterface', function () {

            expect($this->dispatcher)->toBeAnInstanceOf(RequestHandlerInterface::class);

        });

        describe('->process()', function () {

            it('should return the response produced by the request handler', function () {

                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);

                $this->handler->handle->with($request)->returns($response);

                $test = $this->dispatcher->handle($request->get());

                expect($test)->toBe($response->get());

            });

        });

    });

    context('when there is at least one middleware', function () {

        beforeEach(function () {

            $this->handler = mock(RequestHandlerInterface::class);

            $this->dispatcher = new LIFODispatcher($this->handler->get(), ...[
                new TestMiddleware('m1'),
                new TestMiddleware('m2'),
                new TestMiddleware('m3'),
            ]);

        });

        it('should implement RequestHandlerInterface', function () {

            expect($this->dispatcher)->toBeAnInstanceOf(RequestHandlerInterface::class);

        });

        describe('->handle()', function () {

            it('should dispatch the middleware in FIFO order', function () {

                $request1 = mock(ServerRequestInterface::class);
                $request2 = mock(ServerRequestInterface::class);
                $request3 = mock(ServerRequestInterface::class);
                $request4 = mock(ServerRequestInterface::class);

                $response1 = mock(ResponseInterface::class);
                $response2 = mock(ResponseInterface::class);
                $response3 = mock(ResponseInterface::class);
                $response4 = mock(ResponseInterface::class);

                $request1->withHeader->with('test', 'm3')->returns($request2);
                $request2->withHeader->with('test', 'm2')->returns($request3);
                $request3->withHeader->with('test', 'm1')->returns($request4);

                $response1->withHeader->with('test', 'm1')->returns($response2);
                $response2->withHeader->with('test', 'm2')->returns($response3);
                $response3->withHeader->with('test', 'm3')->returns($response4);

                $this->handler->handle->with($request4)->returns($response1);

                $test = $this->dispatcher->handle($request1->get());

                expect($test)->toBe($response4->get());

            });

        });

    });

});
