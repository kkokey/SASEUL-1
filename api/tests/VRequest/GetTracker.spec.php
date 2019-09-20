<?php

use src\System\Key;
use src\System\Version;
use src\VRequest\GetTracker;

describe('GetTracker', function () {
    given('getTracker', function () {
        return new GetTracker();
    });

    describe('getValidity', function () {
        function getKeyRing($msg)
        {
            $hashed_msg = hash('sha256', json_encode($msg));
            $private_key = Key::makePrivateKey();
            $public_key = Key::makePublicKey($private_key);

            return [
                'hashed_msg' => $hashed_msg,
                'private_key' => $private_key,
                'public_key' => $public_key,
                'signature' => Key::makeSignature($hashed_msg, $private_key, $public_key),
                'address' => Key::makeAddress($public_key),
            ];
        }

        given('keyRing', function () {
            return getKeyRing('test_msg');
        });

        given('other_key_ring', function () {
            return getKeyRing('other_msg');
        });

        given('address', function () {
            return $this->keyRing['address'];
        });

        given('public_key', function () {
            return $this->keyRing['public_key'];
        });

        given('signature', function () {
            return $this->keyRing['signature'];
        });

        given('request_from_address', function () {
            return $this->keyRing['address'];
        });

        given('request_type', function () {
            return GetTracker::TYPE;
        });

        given('request_timestamp', function () {
            return 1;
        });

        given('request_version', function () {
            return 'test_version';
        });

        given('request', function () {
            return [
                'type' => $this->request_type,
                'version' => $this->request_version,
                'from' => $this->request_from_address,
                'transactional_data' => 'test_data',
                'timestamp' => $this->request_timestamp,
            ];
        });

        given('subject', function () {
            $this->getTracker->initialize(
                $this->request,
                $this->keyRing['hashed_msg'],
                $this->public_key,
                $this->signature
            );

            return $this->getTracker->getValidity();
        });

        context('with a valid state', function () {
            it('returns true', function () {
                expect($this->subject)->toBeTruthy();
            });
        });

        context('with an invalid request', function () {
            context('with an invalid version string', function () {
                given('request_version', function () {
                    return str_repeat('A', Version::LENGTH_LIMIT + 1);
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid timestamp', function () {
                given('request_timestamp', function () {
                    return '';
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an unmatched type', function () {
                given('request_type', function () {
                    return 'asdf';
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });

            context('with an invalid from address', function () {
                given('request_from_address', function () {
                    return $this->other_key_ring['address'];
                });

                it('returns false', function () {
                    expect($this->subject)->toBeFalsy();
                });
            });
        });

        context('with an invalid public key', function () {
            given('public_key', function () {
                return $this->other_key_ring['public_key'];
            });

            it('returns false', function () {
                expect($this->subject)->toBeFalsy();
            });
        });

        context('with an invalid signature', function () {
            given('signature', function () {
                return $this->other_key_ring['signature'];
            });

            it('returns false', function () {
                expect($this->subject)->toBeFalsy();
            });
        });
    });
});
