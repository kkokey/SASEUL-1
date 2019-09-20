<?php

namespace src\Script\Test;

use src\Script;
use src\System\Tracker;
use src\Util\RestCall;

class DisplayRound extends Script
{
    private $rest;

    public function __construct()
    {
        $this->rest = new RestCall();
    }

    public function _process()
    {
        $validators = Tracker::GetValidator();
        $rounds = [];

        foreach ($validators as $validator) {
            $address = $validator['address'];
            $round = $this->RequestRound($validator['host']);
            $rounds[$address] = $round['decision'];
        }

        $this->data = $rounds;
    }

    public function RequestRound(string $host)
    {
        $url = "http://{$host}/vrequest/getround";

        $rs = $this->rest->POST($url);
        $rs = json_decode($rs, true);

        if (!isset($rs['data'])) {
            return null;
        }

        return $rs['data'];
    }
}
