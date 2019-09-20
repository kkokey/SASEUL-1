<?php

use src\System\Key;

describe('Key', function () {
    given('privateKey', function () {
        return 'b0ffa2793ae5f5aff5bda4cfc7b4872f541364527f386459000587189b0ddbd7';
    });

    given('publicKey', function(){
        return '7878bcdc25612d9cb5f88e9c699d110e9e2d78b8a8f7a262cf429d7b5a7e2045';
    });

    given('address', function(){
        return '0x6fbd7535aa32782e9b3a35d78ffa221d3e5720b8c2e916';
    });

    given('message', function(){
        return 'Hello';
    });

    given('signature', function(){
        return '1ed9a8fcbc1f19ebc5ee2cd5d370a5c242ef8c0a320796c5699e9b1803c43844a93fcc2eac6e94e9294da59216d4988226bc113f060cc94ff3916dc4718c5d07';
    });



    describe('makePublicKey', function () {
        context('with a sample private key', function () {
            it('returns a correct public key', function () {
                expect(Key::makePublicKey($this->privateKey))->toBe($this->publicKey);
            });
        });
    });

    describe('makeAddress', function(){
        context('with a sample public key', function(){
            it('returns a correct address', function(){
                expect(Key::makeAddress($this->publicKey))->toBe($this->address);
            });
        });
    });

    describe('makeSignature', function(){
        context('with a simple message', function(){
            it('returns a correct signature', function(){
                expect(Key::makeSignature($this->message, $this->privateKey, $this->publicKey))->toBe($this->signature);
            });
        });
    });

    describe('isValidSignature', function(){
        context('with a valid signature', function(){
            it('returns true', function(){
                expect(Key::isValidSignature($this->message, $this->publicKey, $this->signature))->toBeTruthy();
            });
        });
    });

    describe('isValidAddress', function(){
        context('with an valid address', function(){
            it('returns true', function(){
                expect(Key::isValidAddress($this->address, $this->publicKey))->toBeTruthy();
            });
        });
    });
});
