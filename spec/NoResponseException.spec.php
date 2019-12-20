<?php

use Quanta\Http\NoResponseException;

describe('NoResponseException', function () {

    it('should implements Throwable', function () {

        expect(new NoResponseException)->toBeAnInstanceOf(Throwable::class);

    });

});
