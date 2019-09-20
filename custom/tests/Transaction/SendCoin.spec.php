<?php

use src\System\Config;
use src\Transaction\SendCoin;

describe('SendCoin', function () {
    beforeAll(function () {
        Config::init();
    });

    given('sendCoin', function () {
        return new SendCoin();
    });

    given('fromBalance', function () {
        return 42;
    });

    given('toBalance', function () {
        return 24;
    });

    given('fee', function () {
        return 1;
    });

    given('subject', function () {
        return $this->sendCoin->isValidDeal($this->fromBalance, $this->toBalance, $this->amount, $this->fee);
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

        context('without any remaining amount to pay the fee', function () {
            given('amount', function () {
                return $this->fromBalance;
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
