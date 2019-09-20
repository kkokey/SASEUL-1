<?php

namespace src\Script\Test;

use src\Script;
use src\System\Block;
use src\System\Tracker;
use src\Util\DateTime;
use src\Util\RestCall;

class DisplayBroadcast extends Script
{
    private $rest;

    public function __construct()
    {
        $this->rest = new RestCall();
    }

    public function _process()
    {
        $validators = Tracker::GetValidator();
        $last_block = Block::GetLastBlock();
        $last_s_timestamp = $last_block['s_timestamp'];
        $s_timestamp = DateTime::Microtime();
        $all_chunks = [];

        foreach ($validators as $validator) {
            $host = $validator['host'];
            $address = $validator['address'];
            $chunks = $this->RequestChunks($host, $last_s_timestamp, $s_timestamp);

            foreach ($chunks as $k => $chunk) {
                unset($chunks[$k]['rows']);
            }

            $all_chunks[$address] = $chunks;
        }

        $this->data = $all_chunks;
    }

    public function RequestChunks($host, $last_s_timestamp, $s_timestamp)
    {
        $url = "http://{$host}/vrequest/getchunks";
        $data = [
            'min_st' => $last_s_timestamp,
            'max_st' => $s_timestamp,
        ];

        $rs = $this->rest->POST($url, $data);
        $rs = json_decode($rs, true);

        if (!isset($rs['data'])) {
            return null;
        }

        return $rs['data'];
    }
}
