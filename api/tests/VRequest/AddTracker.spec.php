<?php

use src\System\Key;
use src\System\Version;
use src\VRequest\AddTracker;

describe('AddTracker', function () {
    given('addTracker', function () {
        return new AddTracker();
    });

    describe('getValidity', function () {
        function getAccount($msg)
        {
            $hashedMsg = hash('sha256', json_encode($msg));
            $privateKey = Key::makePrivateKey();
            $publicKey = Key::makePublicKey($privateKey);

            return [
                'hashedMsg' => $hashedMsg,
                'privateKey' => $privateKey,
                'publicKey' => $publicKey,
                'signature' => Key::makeSignature($hashedMsg, $privateKey, $publicKey),
                'address' => Key::makeAddress($publicKey),
            ];
        }

        given('account', function () {
            return getAccount('testMsg');
        });

        given('otherKeyRing', function () {
            return getAccount('otherMsg');
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

        given('requestHost', function () {
            return '127.0.0.1';
        });

        given('requestType', function () {
            return AddTracker::TYPE;
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
                'host' => $this->requestHost,
                'transactional_data' => 'test_data',
                'timestamp' => $this->requestTimestamp,
            ];
        });

        given('subject', function () {
            $this->addTracker->initialize(
                $this->request,
                $this->account['hashedMsg'],
                $this->publicKey,
                $this->signature
            );

            return $this->addTracker->getValidity();
        });

        context('with a valid state', function () {
            it('returns true', function () {
                expect($this->subject)->toBeTruthy();
            });
        });

        context('with an invalid request', function () {
            context('with an invalid version string', function () {
                given('requestVersion', function () {
                    return str_repeat('A', Version::LENGTH_LIMIT + 1);
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

            context('with an unmatched type', function () {
                given('requestType', function () {
                    return 'asdf';
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid host', function () {
                given('requestHost', function () {
                    return '______';
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid from address', function () {
                given('requestFromAddress', function () {
                    return $this->otherKeyRing['address'];
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });
        });

        context('with an invalid public key', function () {
            given('publicKey', function () {
                return $this->otherKeyRing['publicKey'];
            });

            it('returns false', function () {
                expect($this->subject)->toBeFalsy();
            });
        });

        context('with an invalid signature', function () {
            given('signature', function () {
                return $this->otherKeyRing['signature'];
            });

            it('returns false', function () {
                expect($this->subject)->toBeFalsy();
            });
        });
    });
});
