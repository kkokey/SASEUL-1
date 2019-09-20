<?php

use src\Core\RoundManager;
use src\System\Key;

describe('RoundManager', function () {
    given('roundManager', function () {
        return RoundManager::GetInstance();
    });

    describe('CheckRequest', function () {
        function getDecision($round_number, $last_blockhash)
        {
            return [
                'round_number' => $round_number,
                'last_blockhash' => $last_blockhash,
                'round_key' => RoundManager::GetInstance()->MakeRoundKey($last_blockhash, $round_number)
            ];
        }

        function getKeyRing($decision)
        {
            $hashed_msg = hash('sha256', json_encode($decision));
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

        function getInputValue($decision, $keyRing)
        {
            return [
                'decision' => $decision,
                'public_key' => $keyRing['public_key'],
                'signature' => $keyRing['signature'],
            ];
        }

        beforeEach(function () {
            $round_number = 42;
            $last_blockhash = 'a1023bc';
            $decision = getDecision($round_number, $last_blockhash);
            $keyRing = getKeyRing($decision);

            $this->decision = $decision;
            $this->address = $keyRing['address'];
            $this->value = getInputValue($decision, $keyRing);
        });

        beforeEach(function () {
            $round_number = 1024;
            $last_blockhash = 'ffffff';
            $decision = getDecision($round_number, $last_blockhash);
            $keyRing = getKeyRing($decision);

            $this->other_decision = $decision;
            $this->other_address = $keyRing['address'];
            $this->other_value = getInputValue($decision, $keyRing);
        });

        given('checkRequest', function () {
            return function () {
                return $this->roundManager->CheckRequest($this->address, $this->value);
            };
        });

        context('with a valid address and valid input values', function () {
            it('returns true', function () {
                expect($this->checkRequest())->toBeTruthy();
            });
        });

        context('with an unmatched address', function () {
            given('address', function () {
                return $this->other_address;
            });

            it('returns false', function () {
                expect($this->checkRequest())->toBeFalsy();
            });
        });

        context('with an unmatched input values', function () {
            context('with an unmatched public key', function () {
                beforeEach(function () {
                    $this->value['public_key'] = $this->other_value['public_key'];
                });

                it('returns false', function () {
                    expect($this->checkRequest())->toBeFalsy();
                });
            });

            context('with an unmatched signature', function () {
                beforeEach(function () {
                    $this->value['signature'] = $this->other_value['signature'];
                });

                it('returns false', function () {
                    expect($this->checkRequest())->toBeFalsy();
                });
            });

            context('with an unmatched round number in decision', function () {
                beforeEach(function () {
                    $this->value['decision']['round_number'] = $this->other_value['decision']['round_number'];
                });
                it('returns false', function () {
                    expect($this->checkRequest())->toBeFalsy();
                });
            });

            context('with an unmatched last block hash in decision', function () {
                beforeEach(function () {
                    $this->value['decision']['last_blockhash'] = $this->other_value['decision']['last_blockhash'];
                });
                it('returns false', function () {
                    expect($this->checkRequest())->toBeFalsy();
                });
            });

            context('with an unmatched round key in decision', function () {
                beforeEach(function () {
                    $this->value['decision']['round_key'] = $this->other_value['decision']['round_key'];
                });
                it('returns false', function () {
                    expect($this->checkRequest())->toBeFalsy();
                });
            });
        });
    });
});
