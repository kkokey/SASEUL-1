<?php

use src\System\Config;
use src\System\Key;
use src\System\Version;
use src\VRequest\ListTransactionChunk;

describe('ListTransactionChunk', function () {
    given('listTransactionChunk', function () {
        return new ListTransactionChunk();
    });

    beforeEach(function () {
        Config::init();
    });

    describe('getValidity', function () {
        given('nodeConfig', function () {
            return Config::$node;
        });

        given('account', function () {
            return createAccount('testMsg', Config::$node->getPrivateKey(), Config::$node->getPublicKey());
        });

        given('otherAccount', function () {
            $privateKey = Key::makePrivateKey();
            $publicKey = Key::makePublicKey($privateKey);

            return createAccount('otherMsg', $privateKey, $publicKey);
        });

        given('address', function () {
            return $this->account['address'];
        });

        given('publicKey', function () {
            return $this->account['publicKey'];
        });

        given('signature', function () {
            return $this->account['signature'];
        });

        given('requestFromAddress', function () {
            return $this->account['address'];
        });

        given('requestType', function () {
            return ListTransactionChunk::TYPE;
        });

        given('requestValue', function () {
            return 1;
        });

        given('requestTimestamp', function () {
            return 1;
        });

        given('requestVersion', function () {
            return 'test_version';
        });

        given('request', function () {
            return [
                'type' => $this->requestType,
                'version' => $this->requestVersion,
                'from' => $this->requestFromAddress,
                'value' => $this->requestValue,
                'transactional_data' => 'test_data',
                'timestamp' => $this->requestTimestamp,
            ];
        });

        given('subject', function () {
            $this->listTransactionChunk->initialize(
                $this->request,
                $this->account['hashedMsg'],
                $this->publicKey,
                $this->signature
            );

            return $this->listTransactionChunk->getValidity();
        });

        context('with a valid state', function () {
            it('returns true', function () {
                expect($this->subject)->toBeTruthy();
            });
        });

        context('with an invalid request', function () {
            context('with an unmatched type', function () {
                given('requestType', function () {
                    return 'asdf';
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid version string', function () {
                given('requestVersion', function () {
                    return str_repeat('A', Version::LENGTH_LIMIT + 1);
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an address of non-full node', function () {
                given('requestFromAddress', function () {
                    return $this->otherAccount['address'];
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid value', function () {
                given('requestValue', function () {
                    return 0;
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid timestamp', function () {
                given('requestTimestamp', function () {
                    return '';
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid public key', function () {
                given('publicKey', function () {
                    return $this->otherAccount['publicKey'];
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid signature', function () {
                given('signature', function () {
                    return $this->otherAccount['signature'];
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });
        });
    });
});
