<?php

namespace src\Config;

use src\Util\Config;

class Fee
{
    private $rate;
    private $minRate;

    public function __construct()
    {
        $this->rate = Config::getFromEnv('FEE_RATE');
        $this->minRate = Config::getFromEnv('FEE_RATE_MIN');
    }

    public function getRate()
    {
        echo is_string($this->rate);
        echo is_float($this->rate);

        return $this->rate;
    }

    public function getMinRate()
    {
        return $this->minRate;
    }
}
