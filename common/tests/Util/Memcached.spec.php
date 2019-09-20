<?php

use src\Util\Memcached;

describe('Memcached', function () {
    given('mem', function () {
        return new Memcached();
    });

    given('matchData', function () {
        return 'memcached';
    });

    describe('set', function () {
        given('subject', function () {
            return $this->mem->set('db', $this->matchData);
        });

        it('expects the input value and return value to be the same.', function () {
            expect($this->subject)->toEqual($this->matchData);
        });
    });

    describe('delete', function () {
        context('with cached data', function () {
            beforeEach(function () {
                $this->mem->set('db', 'memcached', 10);
            });

            it('return true', function () {
                expect($this->mem->delete('db'))->toBeTruthy();
            });
        });

        context('without cached data', function () {
            beforeEach(function () {
                $this->mem->delete('db');
            });

            it('return false', function () {
                expect($this->mem->delete('db'))->toBeFalsy();
            });
        });
    });

    describe('get', function () {
        context('with cached data', function () {
            beforeEach(function () {
                $this->mem->set('db', $this->matchData);
            });

            it('returns the value that is set.', function () {
                expect($this->mem->get('db'))->toEqual($this->matchData);
            });
        });

        context('without cached data', function () {
            beforeEach(function () {
                $this->mem->delete('db');
            });

            it('return false', function () {
                expect($this->mem->get('db'))->toBeFalsy();
            });
        });
    });
});
