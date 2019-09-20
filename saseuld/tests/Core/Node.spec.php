<?php

use src\Core\Node;

describe('Node', function () {
    beforeEach(function () {
        $this->node = new Node();
    });

    describe('increaseFailCount', function () {
        context('When called once', function () {
            it('increases 1 of fail counts', function () {
                $before = $this->node->getFailCount();
                $this->node->increaseFailCount();

                expect($this->node->getFailCount())->toEqual($before + 1);
            });
        });

        context('When calling more than once', function () {
            it('Increase the fail count by 1 for each call.', function () {
                $before = $this->node->getFailCount();
                $this->node->increaseFailCount();
                $this->node->increaseFailCount();
                $this->node->increaseFailCount();

                expect($this->node->getFailCount())->toEqual($before + 3);
            });
        });
    });

    describe('resetFailCount', function () {
        context('when called it', function () {
            it('resets fail count to 0', function () {
                $this->node->resetFailCount();

                expect($this->node->getFailCount())->toEqual(0);
            });
        });

        context('When the fail count is not zero', function () {
            beforeEach(function () {
                $this->node->resetFailCount();
                $this->node->increaseFailCount();
            });

            it('resets fail count to 0', function () {
                $this->node->resetFailCount();

                expect($this->node->getFailCount())->toEqual(0);
            });
        });
    });

    describe('isTimeToSeparation', function () {
        context('When the fail count is less than 10', function () {
            beforeEach(function () {
                $this->node->resetFailCount();
                for ($i = 0; $i < 9; $i++) {
                    $this->node->increaseFailCount();
                }
            });

            it('returns false', function () {
                expect($this->node->isTimeToSeparation())->toBe(false);
            });
        });

        context('When the fail count is 10 or more', function () {
            beforeEach(function () {
                $this->node->resetFailCount();
                for ($i = 0; $i < 10; $i++) {
                    $this->node->increaseFailCount();
                }
            });

            it('returns true', function () {
                expect($this->node->isTimeToSeparation())->toBe(true);
            });
        });
    });
});
