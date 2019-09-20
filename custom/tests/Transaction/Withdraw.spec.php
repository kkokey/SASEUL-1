<?php

use src\System\Config;
use src\Transaction\Withdraw;

describe('Withdraw', function () {
    beforeAll(function () {
        Config::init();
    });

    given('withdraw', function () {
        return new Withdraw();
    });

    given('from_deposit', function () {
        return 50;
    });

    given('from_balance', function () {
        return 50;
    });

    describe('isValidTotal', function () {
        context('when the sum of amount and fee is less than from deposit', function () {
            given('amount', function () {
                return 10;
            });

            given('fee', function () {
                return 30;
            });

            it('returns true', function () {
                expect($this->withdraw->isValidAmountWithFee($this->amount + $this->fee, $this->from_deposit))->toBeTruthy();
            });
        });

        context('when the sum of amount and fee is bigger than from deposit', function () {
            given('amount', function () {
                return 30;
            });

            given('fee', function () {
                return 30;
            });

            it('returns false', function () {
                expect($this->withdraw->isValidAmountWithFee($this->amount + $this->fee, $this->from_deposit))->toBeFalsy();
            });
        });

        context('when the sum of amount and fee and from deposit are the same', function () {
            given('amount', function () {
                return 20;
            });

            given('fee', function () {
                return 30;
            });

            it('returns true', function () {
                expect($this->withdraw->isValidAmountWithFee($this->amount + $this->fee, $this->from_deposit))->toBeTruthy();
            });
        });
    });

    describe('isValidTotal', function () {
        context('when the sum of amount and fee is less than from balance', function () {
            given('amount', function () {
                return 10;
            });

            given('fee', function () {
                return 30;
            });

            it('returns true', function () {
                expect($this->withdraw->isValidAmountWithFee($this->amount + $this->fee, $this->from_balance))->toBeTruthy();
            });
        });

        context('when the sum of amount and fee is bigger than from balance', function () {
            given('amount', function () {
                return 30;
            });

            given('fee', function () {
                return 30;
            });

            it('returns false', function () {
                expect($this->withdraw->isValidAmountWithFee($this->amount + $this->fee, $this->from_balance))->toBeFalsy();
            });
        });

        context('when the sum of amount and fee and from balance are the same', function () {
            given('amount', function () {
                return 20;
            });

            given('fee', function () {
                return 30;
            });

            it('returns true', function () {
                expect($this->withdraw->isValidAmountWithFee($this->amount + $this->fee, $this->from_balance))->toBeTruthy();
            });
        });
    });
});
