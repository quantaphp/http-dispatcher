<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\Dispatcher;
use Quanta\Http\AbstractDispatcher;
use Quanta\Http\FallbackDispatcher;
use Quanta\Http\NoResponseException;

final class TestMiddleware implements MiddlewareInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withHeader('test', $this->value);

        $response = $handler->handle($request);

        return $response->withHeader('test', $this->value);
    }
}

describe('Dispatcher::stack()', function () {

    context('when no middleware are given', function () {

        it('should return a FallbackDispatcher', function () {

            $test = Dispatcher::stack();

            expect($test)->toBeAnInstanceOf(FallbackDispatcher::class);

        });

    });

    context('when at least one middleware is given', function () {

        it('should return a dispatcher with the given middleware in LIFO order', function () {

            $middleware1 = mock(MiddlewareInterface::class);
            $middleware2 = mock(MiddlewareInterface::class);
            $middleware3 = mock(MiddlewareInterface::class);

            $test = Dispatcher::stack(
                $middleware1->get(),
                $middleware2->get(),
                $middleware3->get(),
            );

            expect($test)->toEqual(
                new Dispatcher(
                    new Dispatcher(
                        new Dispatcher(
                            new FallbackDispatcher,
                            $middleware1->get(),
                        ),
                        $middleware2->get(),
                    ),
                    $middleware3->get(),
                )
            );

        });

    });

});

describe('Dispatcher::queue()', function () {

    context('when no middleware are given', function () {

        it('should return a FallbackDispatcher', function () {

            $test = Dispatcher::queue();

            expect($test)->toBeAnInstanceOf(FallbackDispatcher::class);

        });

    });

    context('when at least one middleware is given', function () {

        it('should return a dispatcher with the given middleware in FIFO order', function () {

            $middleware1 = mock(MiddlewareInterface::class);
            $middleware2 = mock(MiddlewareInterface::class);
            $middleware3 = mock(MiddlewareInterface::class);

            $test = Dispatcher::queue(
                $middleware1->get(),
                $middleware2->get(),
                $middleware3->get(),
            );

            expect($test)->toEqual(
                new Dispatcher(
                    new Dispatcher(
                        new Dispatcher(
                            new FallbackDispatcher,
                            $middleware3->get(),
                        ),
                        $middleware2->get(),
                    ),
                    $middleware1->get(),
                )
            );

        });

    });

});

describe('Dispatcher', function () {

    beforeEach(function () {
        $this->handler = mock(AbstractDispatcher::class);
        $this->middleware = mock(MiddlewareInterface::class);

        $this->dispatcher = new Dispatcher(
            $this->handler->get(),
            $this->middleware->get(),
        );
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

        context('when the inner middleware does not need a request handler to produce a response', function () {

            beforeEach(function () {
                $this->dispatcher = mock(AbstractDispatcher::class);
                $this->middleware = mock(MiddlewareInterface::class);
            });

            it('should return the response produced by the inner middleware', function () {

                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);

                $handler = mock(RequestHandlerInterface::class);

                $this->middleware->process->with($request, $this->dispatcher)->returns($response);

                $dispatcher = new Dispatcher($this->dispatcher->get(), $this->middleware->get());

                $test = $dispatcher->process($request->get(), $handler->get());

                expect($test)->toBe($response->get());

            });

        });

        context('when the inner middleware needs a request handler to produce a response', function () {

            beforeEach(function () {
                $this->middleware = new TestMiddleware('value1');
            });

            context('when the inner dispatcher is an instance of FallbackDispatcher', function () {

                it('should return the response produced by the inner middleware and the given request handler', function () {

                    $dispatcher = new FallbackDispatcher;

                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);
                    $response1 = mock(ResponseInterface::class);
                    $response2 = mock(ResponseInterface::class);

                    $handler = mock(RequestHandlerInterface::class);

                    $request1->withHeader->with('test', 'value1')->returns($request2);
                    $response1->withHeader->with('test', 'value1')->returns($response2);

                    $handler->handle->with($request2)->returns($response1);

                    $dispatcher = new Dispatcher($dispatcher, $this->middleware);

                    $test = $dispatcher->process($request1->get(), $handler->get());

                    expect($test)->toBe($response2->get());

                });

            });

            context('when the inner dispatcher is an instance of Dispatcher', function () {

                it('should return the response produced by the inner middleware and the given request handler', function () {

                    $dispatcher = new Dispatcher(new FallbackDispatcher, new TestMiddleware('value2'));

                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);
                    $request3 = mock(ServerRequestInterface::class);
                    $response1 = mock(ResponseInterface::class);
                    $response2 = mock(ResponseInterface::class);
                    $response3 = mock(ResponseInterface::class);

                    $handler = mock(RequestHandlerInterface::class);

                    $request1->withHeader->with('test', 'value1')->returns($request2);
                    $request2->withHeader->with('test', 'value2')->returns($request3);
                    $response1->withHeader->with('test', 'value2')->returns($response2);
                    $response2->withHeader->with('test', 'value1')->returns($response3);

                    $handler->handle->with($request3)->returns($response1);

                    $dispatcher = new Dispatcher($dispatcher, $this->middleware);

                    $test = $dispatcher->process($request1->get(), $handler->get());

                    expect($test)->toBe($response3->get());

                });

            });

        });

    });

    describe('->handle()', function () {

        context('when the inner middleware does not need a request handler to produce a response', function () {

            beforeEach(function () {
                $this->dispatcher = mock(AbstractDispatcher::class);
                $this->middleware = mock(MiddlewareInterface::class);
            });

            it('should return the response produced by the inner middleware', function () {

                $request = mock(ServerRequestInterface::class);
                $response = mock(ResponseInterface::class);

                $this->middleware->process->with($request, $this->dispatcher)->returns($response);

                $dispatcher = new Dispatcher($this->dispatcher->get(), $this->middleware->get());

                $test = $dispatcher->handle($request->get());

                expect($test)->toBe($response->get());

            });

        });

        context('when the inner middleware needs a request handler to produce a response', function () {

            beforeEach(function () {
                $this->middleware = new TestMiddleware('value1');
            });

            context('when the inner dispatcher is an instance of FallbackDispatcher', function () {

                it('should throw a NoResponseException', function () {

                    $dispatcher = new FallbackDispatcher;

                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);

                    $request1->withHeader->with('test', 'value1')->returns($request2);

                    $dispatcher = new Dispatcher($dispatcher, $this->middleware);

                    $test = fn () => $dispatcher->handle($request1->get());

                    expect($test)->toThrow(new NoResponseException);

                });

            });

            context('when the inner dispatcher is an instance of Dispatcher', function () {

                it('should throw a NoResponseException', function () {

                    $dispatcher = new Dispatcher(new FallbackDispatcher, new TestMiddleware('value2'));

                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);
                    $request3 = mock(ServerRequestInterface::class);

                    $request1->withHeader->with('test', 'value1')->returns($request2);
                    $request2->withHeader->with('test', 'value2')->returns($request3);

                    $dispatcher = new Dispatcher($dispatcher, $this->middleware);

                    $test = fn () => $dispatcher->handle($request1->get());

                    expect($test)->toThrow(new NoResponseException);

                });

            });

        });

    });

});
