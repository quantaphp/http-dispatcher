<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\Dispatcher;
use Quanta\Http\NoResponseRequestHandler;

require_once __DIR__ . '/classes/TestMiddleware.php';

describe('Dispatcher', function () {

    context('when no middleware is given', function () {

        beforeEach(function () {
            $this->dispatcher = new Dispatcher;
        });

        it('should implement MiddlewareInterface', function () {
            expect($this->dispatcher)->toBeAnInstanceOf(MiddlewareInterface::class);
        });

        it('should implement RequestHandlerInterface', function () {
            expect($this->dispatcher)->toBeAnInstanceOf(RequestHandlerInterface::class);
        });

        describe('->process()', function () {

            it('should return the response produced by the given request handler', function () {
                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);
                $handler = mock(RequestHandlerInterface::class);

                $handler->handle->with($request)->returns($response);

                $test = $this->dispatcher->process($request->get(), $handler->get());

                expect($test)->toBe($response->get());
            });

        });

        describe('->handle()', function () {

            it('should throw an Exception', function () {
                $request = mock(ServerRequestInterface::class);

                $test = fn () => $this->dispatcher->handle($request->get());

                expect($test)->toThrow(new Exception);
            });

        });

    });

    context('when at least one middleware is given', function () {

        it('should implement MiddlewareInterface', function () {
            $test = new Dispatcher(mock(MiddlewareInterface::class)->get());

            expect($test)->toBeAnInstanceOf(MiddlewareInterface::class);
        });

        it('should implement RequestHandlerInterface', function () {
            $test = new Dispatcher(mock(MiddlewareInterface::class)->get());

            expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);
        });

        describe('->process()', function () {

            it('should return the response produced by the middleware and the given request handler', function () {
                $request1 = mock(ServerRequestInterface::class);
                $request2 = mock(ServerRequestInterface::class);
                $request3 = mock(ServerRequestInterface::class);
                $request4 = mock(ServerRequestInterface::class);
                $response1 = mock(ResponseInterface::class);
                $response2 = mock(ResponseInterface::class);
                $response3 = mock(ResponseInterface::class);
                $response4 = mock(ResponseInterface::class);

                $handler = mock(RequesthandlerInterface::class);

                $dispatcher = new Dispatcher(
                    new Test\TestMiddleware('value1'),
                    new Test\TestMiddleware('value2'),
                    new Test\TestMiddleware('value3'),
                );

                $request1->withHeader->with('test', 'value1')->returns($request2);
                $request2->withHeader->with('test', 'value2')->returns($request3);
                $request3->withHeader->with('test', 'value3')->returns($request4);
                $response1->withHeader->with('test', 'value3')->returns($response2);
                $response2->withHeader->with('test', 'value2')->returns($response3);
                $response3->withHeader->with('test', 'value1')->returns($response4);

                $handler->handle->with($request4)->returns($response1);

                $test = $dispatcher->process($request1->get(), $handler->get());

                expect($test)->toBe($response4->get());
            });

        });

        describe('->handle()', function () {

            context('when a middleware returns a response', function () {

                it('should return the response retured by the middleware', function () {
                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);
                    $request3 = mock(ServerRequestInterface::class);
                    $request4 = mock(ServerRequestInterface::class);
                    $response1 = mock(ResponseInterface::class);
                    $response2 = mock(ResponseInterface::class);
                    $response3 = mock(ResponseInterface::class);
                    $response4 = mock(ResponseInterface::class);

                    $middleware = mock(MiddlewareInterface::class);

                    $dispatcher = new Dispatcher(
                        new Test\TestMiddleware('value1'),
                        new Test\TestMiddleware('value2'),
                        new Test\TestMiddleware('value3'),
                        $middleware->get(),
                    );

                    $request1->withHeader->with('test', 'value1')->returns($request2);
                    $request2->withHeader->with('test', 'value2')->returns($request3);
                    $request3->withHeader->with('test', 'value3')->returns($request4);
                    $response1->withHeader->with('test', 'value3')->returns($response2);
                    $response2->withHeader->with('test', 'value2')->returns($response3);
                    $response3->withHeader->with('test', 'value1')->returns($response4);

                    $middleware->process->with($request4, '*')->returns($response1);

                    $test = $dispatcher->handle($request1->get());

                    expect($test)->toBe($response4->get());
                });

            });

            context('when no middleware return a response', function () {

                it('should throw an Exception', function () {
                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);
                    $request3 = mock(ServerRequestInterface::class);
                    $request4 = mock(ServerRequestInterface::class);

                    $dispatcher = new Dispatcher(
                        new Test\TestMiddleware('value1'),
                        new Test\TestMiddleware('value2'),
                        new Test\TestMiddleware('value3'),
                    );

                    $request1->withHeader->with('test', 'value1')->returns($request2);
                    $request2->withHeader->with('test', 'value2')->returns($request3);
                    $request3->withHeader->with('test', 'value3')->returns($request4);

                    $test = fn () => $dispatcher->handle($request1->get());

                    expect($test)->toThrow(new Exception);
                });

            });

        });

    });

});
