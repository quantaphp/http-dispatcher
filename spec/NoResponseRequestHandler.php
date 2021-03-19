<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\NoResponseRequestHandler;

describe('NoResponseRequestHandler', function () {

    beforeEach(function () {
        $this->handler = new NoResponseRequestHandler;
    });

    it('should implements RequestHandlerInterface', function () {
        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);
    });

    describe('->handle()', function () {

        it('should throw an exception', function () {
            $request = mock(ServerRequestInterface::class);

            $test = fn () => $this->handler->handle($request->get());

            expect($test)->toThrow(new Exception);
        });

    });

});
