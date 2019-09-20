<?php

use src\System\Config;
use src\Transaction\MakeCoin;

describe('MakeCoin', function () {
    beforeAll(function () {
        $value = $this->genesisCoin;
        putenv("GENESIS_COIN_VALUE=${value}");
        Config::init();
    });

    given('makeCoin', function () {
        return new MakeCoin();
    });

    given('genesisCoin', function () {
        return Config::$genesis->getCoinValue();
    });

    describe('isValidAmount', function () {
        context('with right amount', function () {
            given('amount', function () {
                return 1;
            });
            it('returns true', function () {
                expect($this->makeCoin->isValidAmount($this->amount))->toBeTruthy();
            });
        });

        context('with zero amount', function () {
            given('amount', function () {
                return 0;
            });
            it('returns false', function () {
                expect($this->makeCoin->isValidAmount($this->amount))->toBeFalsy();
            });
        });

        context('with minus amount', function () {
            given('amount', function () {
                return -1;
            });
            it('returns false', function () {
                expect($this->makeCoin->isValidAmount($this->amount))->toBeFalsy();
            });
        });

        context('with much amount', function () {
            given('amount', function () {
                return $this->genesisCoin + 1;
            });
            it('returns false', function () {
                expect($this->makeCoin->isValidAmount($this->amount))->toBeFalsy();
            });
        });
    });
});
