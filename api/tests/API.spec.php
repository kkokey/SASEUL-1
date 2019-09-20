<?php

use src\API;
use src\System\Terminator;

describe('API', function () {
    given('api', function () {
        return new API();
    });

    describe('getParam', function () {
        given('request', function () {
            return [
                'public_key' => 'diaekdideh1230didk495k',
                'transaction' => "{'a': 1, 'b': 'string'}",
                'amount' => 2000,
            ];
        });

        context('with get the key and value in the request', function () {
            it('returns a value paired with the key', function () {
                expect($this->api->getParam($this->request, 'amount'))->toBe($this->request['amount']);
                expect($this->api->getParam($this->request, 'public_key'))->toBe($this->request['public_key']);
            });
        });

        context('with the default value and the wrong key string', function () {
            it('returns a value paired with the key', function () {
                expect($this->api->getParam($this->request, 'st', ['default' => 0]))->toBe(0);
                expect($this->api->getParam($this->request, 'uname', ['default' => '']))->toBe('');
            });
        });

        context('when defining a type you want', function () {
            it('returns string type value', function () {
                expect($this->api->getParam($this->request, 'public_key', ['type' => 'string']))
                    ->toBe($this->request['public_key']);
            });

            it('returns numeric type value', function () {
                expect($this->api->getParam($this->request, 'amount', ['type' => 'numeric']))
                    ->toBe($this->request['amount']);
            });
        });

        context('when there is empty request value and set type value', function () {
            given('request', function () {
                return [];
            });

            it('return a default value', function () {
                expect($this->api->getParam($this->request, 'amount', ['default' => 1000, 'type' => 'numeric']))
                    ->toBe(1000);
            });
        });
    });

    describe('checkType', function () {
        given('stringParam', function () {
            return 'str';
        });

        given('numericParam', function () {
            return 42;
        });

        context('with checking the string value', function () {
            it('return true', function () {
                expect($this->api->checkType($this->stringParam, 'string'))->toBeTruthy();
            });

            it('return false', function () {
                expect($this->api->checkType($this->numericParam, 'string'))->toBeFalsy();
            });
        });

        context('with checking the numeric value', function () {
            it('return true', function () {
                expect($this->api->checkType($this->numericParam, 'numeric'))->toBeTruthy();
            });

            it('return false', function () {
                expect($this->api->checkType($this->stringParam, 'numeric'))->toBeFalsy();
            });
        });
    });

    describe('Error403', function () {
        beforeEach(function () {
            Terminator::setTestMode();
        });

        context('when call', function () {
            it('throws an Exception', function () {
                expect(function () {
                    $this->api->Error403();
                })->toThrow(new Exception('exit'));
            });
        });
    });

    describe('Error404', function () {
        beforeEach(function () {
            Terminator::setTestMode();
        });

        context('when call', function () {
            it('throws an Exception', function () {
                expect(function () {
                    $this->api->Error404();
                })->toThrow(new Exception('exit'));
            });
        });
    });

    describe('Error', function () {
        beforeEach(function () {
            Terminator::setTestMode();
        });

        context('when call', function () {
            it('throws an Exception', function () {
                expect(function () {
                    $this->api->Error();
                })->toThrow(new Exception('exit'));
            });
        });
    });
});
