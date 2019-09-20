<?php

use src\Util\Config;

describe('Config', function () {
    describe('getFromEnv', function () {
        context('with empty environment value', function () {
            it('return false', function () {
                expect(Config::getFromEnv('ANSWER_LIFE'))->toBeFalsy();
            });
        });

        context('with answer life environment value', function () {
            beforeEach(function () {
                putenv('ANSWER_LIFE=42');
            });

            it('return true', function () {
                expect(Config::getFromEnv('ANSWER_LIFE'))->toEqual('42');
            });
        });
    });
});
