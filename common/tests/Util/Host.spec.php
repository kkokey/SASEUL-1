<?php

use src\System\Config;
use src\Util\Host;

describe('Host', function () {
    describe('isValid', function () {
        context('with an int value', function () {
            it('returns false', function () {
                expect(Host::isValid(2000000))->toBeFalsy();
            });
        });

        context('with an empty string', function () {
            it('returns false', function () {
                expect(Host::isValid(''))->toBeFalsy();
            });
        });

        context('with a valid IP address', function () {
            given('ip', function () {
                return '192.168.0.1';
            });

            it('returns true', function () {
                expect(Host::isValid($this->ip))->toBeTruthy();
            });
        });

        context('with a wrong formatted IP address', function () {
            given('ip', function () {
                return '-192.168.0.4';
            });

            it('returns false', function () {
                expect(Host::isValid($this->ip))->toBeFalsy();
            });
        });

        context('with a valid domain address', function () {
            given('hosts', function () {
                return ['mysaseul.net', 'origin-node.saseul.net', 'dev.alice.saseul.net'];
            });

            it('returns true', function () {
                foreach ($this->hosts as $host) {
                    expect(Host::isValid($host))->toBeTruthy();
                }
            });
        });

        context('with a wrong formatted domain address', function () {
            given('hosts', function () {
                return ['-naver.naver.com', '*.saseul.net', 'dev.*.saseul.net'];
            });

            it('return false', function () {
                foreach ($this->hosts as $host) {
                    expect(Host::isValid($host))->toBeFalsy();
                }
            });
        });
    });

    describe('isValidAddress', function () {
        given('hostAddress', function () {
            Config::init();

            return Config::$node->getAddress();
        });

        it('return true', function () {
            expect(Host::isValidAddress($this->hostAddress));
        });

        it('return false', function () {
            expect(Host::isValidAddress('0x06000000000001'));
        });
    });
});
