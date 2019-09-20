<?php

namespace src\Script;

use src\Script;
use src\Util\Logger;

class Test extends Script
{
    public function _process()
    {
        $a = [];

        $a['dd']['22'] = '244';

        Logger::Log(isset($a['ss']['22']));
    }

    public function Test1()
    {
        $orders = [];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 111, 'timestamp' => 1];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 444, 'timestamp' => 13];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 222, 'timestamp' => 2];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 111, 'timestamp' => 12];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 111, 'timestamp' => 8];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 333, 'timestamp' => 11];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 444, 'timestamp' => 4];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 222, 'timestamp' => 6];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 333, 'timestamp' => 3];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 111, 'timestamp' => 5];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 333, 'timestamp' => 7];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 444, 'timestamp' => 9];
        $orders[] = ['id' => bin2hex(random_bytes(24)), 'rate' => 222, 'timestamp' => 10];

        $rate = [];
        $timestamp = [];

        foreach ($orders as $item) {
            $rate[] = $item['rate'];
            $timestamp[] = $item['timestamp'];
        }

        Logger::Log($orders);
        array_multisort($rate, $timestamp, $orders);

        Logger::Log($orders);
        Logger::Log($rate);
        Logger::Log($timestamp);
    }
}
