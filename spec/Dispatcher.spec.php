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

    context('when built as a stack', function () {

        it('should implement MiddlewareInterface', function () {
            $test = Dispatcher::stack();

            expect($test)->toBeAnInstanceOf(MiddlewareInterface::class);
        });

        it('should implement RequestHandlerInterface', function () {
            $test = Dispatcher::stack();

            expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);
        });

        context('when no middleware is given', function () {

            describe('->process()', function () {

                it('should return the response produced by the given request handler with the given request', function () {
                    $request = mock(ServerRequestInterface::class);
                    $response = mock(ResponseInterface::class);
                    $handler = mock(RequestHandlerInterface::class);

                    $handler->handle->with($request)->returns($response);

                    $dispatcher = Dispatcher::stack();

                    $test = $dispatcher->process($request->get(), $handler->get());

                    expect($test)->toBe($response->get());
                });

            });

            describe('->handle()', function () {

                it('should throw an exception', function () {
                    $request = mock(ServerRequestInterface::class);

                    $dispatcher = Dispatcher::stack();

                    $test = fn () => $dispatcher->handle($request->get());

                    expect($test)->toThrow(new Exception(NoResponseRequestHandler::MESSAGE));
                });

            });

        });

        context('when at least one middleware is given', function () {

            describe('->process()', function () {

                context('when a middleware produces a response', function () {

                    it('should return the response produced by the middleware', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);
                        $response1 = mock(ResponseInterface::class);
                        $response2 = mock(ResponseInterface::class);
                        $response3 = mock(ResponseInterface::class);
                        $response4 = mock(ResponseInterface::class);
                        $middleware = mock(MiddlewareInterface::class);
                        $handler = mock(RequesthandlerInterface::class);

                        $request1->withHeader->with('test', 'value3')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value1')->returns($request4);
                        $response1->withHeader->with('test', 'value1')->returns($response2);
                        $response2->withHeader->with('test', 'value2')->returns($response3);
                        $response3->withHeader->with('test', 'value3')->returns($response4);

                        $middleware->process
                            ->with($request4, Kahlan\Arg::toBeAnInstanceOf(RequestHandlerInterface::class))
                            ->returns($response1);

                        $dispatcher = Dispatcher::stack(
                            $middleware->get(),
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                        );

                        $test = $dispatcher->process($request1->get(), $handler->get());

                        expect($test)->toBe($response4->get());
                    });

                });

                context('when no middleware return a response', function () {

                    it('should return the response produced by the given request handler', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);
                        $response1 = mock(ResponseInterface::class);
                        $response2 = mock(ResponseInterface::class);
                        $response3 = mock(ResponseInterface::class);
                        $response4 = mock(ResponseInterface::class);
                        $handler = mock(RequesthandlerInterface::class);

                        $request1->withHeader->with('test', 'value3')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value1')->returns($request4);
                        $response1->withHeader->with('test', 'value1')->returns($response2);
                        $response2->withHeader->with('test', 'value2')->returns($response3);
                        $response3->withHeader->with('test', 'value3')->returns($response4);

                        $handler->handle->with($request4)->returns($response1);

                        $dispatcher = Dispatcher::stack(
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                        );

                        $test = $dispatcher->process($request1->get(), $handler->get());

                        expect($test)->toBe($response4->get());
                    });

                });

            });

            describe('->handle()', function () {

                context('when a middleware produces a response', function () {

                    it('should return the response produced by the middleware', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);
                        $response1 = mock(ResponseInterface::class);
                        $response2 = mock(ResponseInterface::class);
                        $response3 = mock(ResponseInterface::class);
                        $response4 = mock(ResponseInterface::class);
                        $middleware = mock(MiddlewareInterface::class);

                        $request1->withHeader->with('test', 'value3')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value1')->returns($request4);
                        $response1->withHeader->with('test', 'value1')->returns($response2);
                        $response2->withHeader->with('test', 'value2')->returns($response3);
                        $response3->withHeader->with('test', 'value3')->returns($response4);

                        $middleware->process
                            ->with($request4, Kahlan\Arg::toBeAnInstanceOf(RequestHandlerInterface::class))
                            ->returns($response1);

                        $dispatcher = Dispatcher::stack(
                            $middleware->get(),
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                        );

                        $test = $dispatcher->handle($request1->get());

                        expect($test)->toBe($response4->get());
                    });

                });

                context('when no middleware return a response', function () {

                    it('should throw an exception', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);

                        $request1->withHeader->with('test', 'value3')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value1')->returns($request4);

                        $dispatcher = Dispatcher::stack(
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                        );

                        $test = fn () => $dispatcher->handle($request1->get());

                        expect($test)->toThrow(new Exception(NoResponseRequestHandler::MESSAGE));
                    });

                });

            });

        });

    });

    context('when built as a queue', function () {

        it('should implement MiddlewareInterface', function () {
            $test = Dispatcher::queue();

            expect($test)->toBeAnInstanceOf(MiddlewareInterface::class);
        });

        it('should implement RequestHandlerInterface', function () {
            $test = Dispatcher::queue();

            expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);
        });

        context('when no middleware is given', function () {

            describe('->process()', function () {

                it('should return the response produced by the given request handler with the given request', function () {
                    $request = mock(ServerRequestInterface::class);
                    $response = mock(ResponseInterface::class);
                    $handler = mock(RequestHandlerInterface::class);

                    $handler->handle->with($request)->returns($response);

                    $dispatcher = Dispatcher::queue();

                    $test = $dispatcher->process($request->get(), $handler->get());

                    expect($test)->toBe($response->get());
                });

            });

            describe('->handle()', function () {

                it('should throw an exception', function () {
                    $request = mock(ServerRequestInterface::class);

                    $dispatcher = Dispatcher::queue();

                    $test = fn () => $dispatcher->handle($request->get());

                    expect($test)->toThrow(new Exception(NoResponseRequestHandler::MESSAGE));
                });

            });

        });

        context('when at least one middleware is given', function () {

            describe('->process()', function () {

                context('when a middleware produces a response', function () {

                    it('should return the response produced by the middleware', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);
                        $response1 = mock(ResponseInterface::class);
                        $response2 = mock(ResponseInterface::class);
                        $response3 = mock(ResponseInterface::class);
                        $response4 = mock(ResponseInterface::class);
                        $middleware = mock(MiddlewareInterface::class);
                        $handler = mock(RequesthandlerInterface::class);

                        $request1->withHeader->with('test', 'value1')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value3')->returns($request4);
                        $response1->withHeader->with('test', 'value3')->returns($response2);
                        $response2->withHeader->with('test', 'value2')->returns($response3);
                        $response3->withHeader->with('test', 'value1')->returns($response4);

                        $middleware->process
                            ->with($request4, Kahlan\Arg::toBeAnInstanceOf(RequestHandlerInterface::class))
                            ->returns($response1);

                        $dispatcher = Dispatcher::queue(
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                            $middleware->get(),
                        );

                        $test = $dispatcher->process($request1->get(), $handler->get());

                        expect($test)->toBe($response4->get());
                    });

                });

                context('when no middleware return a response', function () {

                    it('should return the response produced by the given request handler', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);
                        $response1 = mock(ResponseInterface::class);
                        $response2 = mock(ResponseInterface::class);
                        $response3 = mock(ResponseInterface::class);
                        $response4 = mock(ResponseInterface::class);
                        $handler = mock(RequesthandlerInterface::class);

                        $request1->withHeader->with('test', 'value1')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value3')->returns($request4);
                        $response1->withHeader->with('test', 'value3')->returns($response2);
                        $response2->withHeader->with('test', 'value2')->returns($response3);
                        $response3->withHeader->with('test', 'value1')->returns($response4);

                        $handler->handle->with($request4)->returns($response1);

                        $dispatcher = Dispatcher::queue(
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                        );

                        $test = $dispatcher->process($request1->get(), $handler->get());

                        expect($test)->toBe($response4->get());
                    });

                });

            });

            describe('->handle()', function () {

                context('when a middleware produces a response', function () {

                    it('should return the response produced by the middleware', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);
                        $response1 = mock(ResponseInterface::class);
                        $response2 = mock(ResponseInterface::class);
                        $response3 = mock(ResponseInterface::class);
                        $response4 = mock(ResponseInterface::class);
                        $middleware = mock(MiddlewareInterface::class);

                        $request1->withHeader->with('test', 'value1')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value3')->returns($request4);
                        $response1->withHeader->with('test', 'value3')->returns($response2);
                        $response2->withHeader->with('test', 'value2')->returns($response3);
                        $response3->withHeader->with('test', 'value1')->returns($response4);

                        $middleware->process
                            ->with($request4, Kahlan\Arg::toBeAnInstanceOf(RequestHandlerInterface::class))
                            ->returns($response1);

                        $dispatcher = Dispatcher::queue(
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                            $middleware->get(),
                        );

                        $test = $dispatcher->handle($request1->get());

                        expect($test)->toBe($response4->get());
                    });

                });

                context('when no middleware return a response', function () {

                    it('should throw an exception', function () {
                        $request1 = mock(ServerRequestInterface::class);
                        $request2 = mock(ServerRequestInterface::class);
                        $request3 = mock(ServerRequestInterface::class);
                        $request4 = mock(ServerRequestInterface::class);

                        $request1->withHeader->with('test', 'value1')->returns($request2);
                        $request2->withHeader->with('test', 'value2')->returns($request3);
                        $request3->withHeader->with('test', 'value3')->returns($request4);

                        $dispatcher = Dispatcher::queue(
                            new Test\TestMiddleware('value1'),
                            new Test\TestMiddleware('value2'),
                            new Test\TestMiddleware('value3'),
                        );

                        $test = fn () => $dispatcher->handle($request1->get());

                        expect($test)->toThrow(new Exception(NoResponseRequestHandler::MESSAGE));
                    });

                });

            });

        });

    });

});
