<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\RequestHandler;

require_once __DIR__ . '/classes/TestMiddleware.php';

describe('RequestHandler::stack()', function () {

    beforeEach(function () {
        $this->handler = mock(RequestHandlerInterface::class)->get();
    });

    context('when no middleware is given', function () {

        it('should return the given request handler', function () {

            $test = RequestHandler::stack($this->handler);

            expect($test)->toBe($this->handler);

        });

    });

    context('when at least one middleware is given', function () {

        it('should return a RequestHandler with the given middleware in LIFO order', function () {

            $middleware1 = mock(MiddlewareInterface::class)->get();
            $middleware2 = mock(MiddlewareInterface::class)->get();
            $middleware3 = mock(MiddlewareInterface::class)->get();

            $test = RequestHandler::stack($this->handler,
                $middleware1,
                $middleware2,
                $middleware3,
            );

            expect($test)->toEqual(
                new RequestHandler(
                    new RequestHandler(
                        new RequestHandler($this->handler, $middleware1),
                        $middleware2,
                    ),
                    $middleware3,
                )
            );

        });

    });

});

describe('RequestHandler::queue()', function () {

    beforeEach(function () {
        $this->handler = mock(RequestHandlerInterface::class)->get();
    });

    context('when no middleware is given', function () {

        it('should return the given request handler', function () {

            $test = RequestHandler::queue($this->handler);

            expect($test)->toBe($this->handler);

        });

    });

    context('when at least one middleware is given', function () {

        it('should return a RequestHandler with the given middleware in FIFO order', function () {

            $middleware1 = mock(MiddlewareInterface::class)->get();
            $middleware2 = mock(MiddlewareInterface::class)->get();
            $middleware3 = mock(MiddlewareInterface::class)->get();

            $test = RequestHandler::queue($this->handler,
                $middleware1,
                $middleware2,
                $middleware3,
            );

            expect($test)->toEqual(
                new RequestHandler(
                    new RequestHandler(
                        new RequestHandler($this->handler, $middleware3),
                        $middleware2,
                    ),
                    $middleware1,
                )
            );

        });

    });

});

describe('RequestHandler', function () {

    beforeEach(function () {
        $this->delegate = mock(RequestHandlerInterface::class);

        $middleware = new Test\TestMiddleware('value');

        $this->handler = new RequestHandler($this->delegate->get(), $middleware);
    });

    it('should implements RequestHandlerInterface', function () {

        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->process()', function () {

        it('should return the response produced by the middleware and the request handler', function () {

            $request1 = mock(ServerRequestInterface::class);
            $request2 = mock(ServerRequestInterface::class);
            $response1 = mock(ResponseInterface::class);
            $response2 = mock(ResponseInterface::class);

            $request1->withHeader->with('test', 'value')->returns($request2);
            $response1->withHeader->with('test', 'value')->returns($response2);

            $this->delegate->handle->with($request2)->returns($response1);

            $test = $this->handler->handle($request1->get());

            expect($test)->toBe($response2->get());

        });

    });

});
