<?php

use src\System\Version;

describe('Version', function () {
    describe('isValid', function () {
        context('with a string shorter than limit', function () {
            beforeEach(function () {
                $this->version = str_repeat('A', Version::LENGTH_LIMIT - 1);
            });

            it('returns true', function () {
                expect(Version::isValid($this->version))->toBe(true);
            });
        });

        context('with an integer', function () {
            beforeEach(function () {
                $this->version = 1234;
            });

            it('returns false', function () {
                expect(Version::isValid($this->version))->toBe(false);
            });
        });

        context('with a string with limit length', function () {
            beforeEach(function () {
                $this->version = str_repeat('A', Version::LENGTH_LIMIT);
            });

            it('returns false', function () {
                expect(Version::isValid($this->version))->toBe(false);
            });
        });

        context('with an empty string', function () {
            beforeEach(function () {
                $this->version = '';
            });

            it('returns false', function () {
                expect(Version::isValid($this->version))->toBeFalsy();
            });
        });
    });
});
