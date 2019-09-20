<?php

use src\System\Config;
use src\Transaction\SendToken;

describe('SendToken', function () {
    beforeAll(function () {
        Config::init();
    });

    given('sendCoin', function () {
        return new SendToken();
    });

    given('fromBalance', function () {
        return 42;
    });

    given('toBalance', function () {
        return 24;
    });

    given('subject', function () {
        return $this->sendCoin->isValidDeal($this->fromBalance, $this->toBalance, $this->amount);
    });

    describe('isValidDeal', function () {
        context('with the right deal', function () {
            given('amount', function () {
                return 1;
            });

            it('returns true', function () {
                expect($this->subject)->toBeTruthy();
            });
        });

        context('with amount bigger than from balance', function () {
            given('amount', function () {
                return $this->fromBalance + 1;
            });

            it('returns false', function () {
                expect($this->subject)->toBeFalsy();
            });
        });

        context('with negative amount', function () {
            given('amount', function () {
                return -1;
            });

            it('returns false', function () {
                expect($this->subject)->toBeFalsy();
            });
        });

        context('with zero amount', function () {
            given('amount', function () {
                return 0;
            });

            it('returns false', function () {
                expect($this->subject)->toBeFalsy();
            });
        });
    });
});
