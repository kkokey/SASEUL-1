<?php

namespace src\Script;

use src\Script;
use src\System\Config;
use src\Util\Logger;

class MyAddress extends Script
{
    public function _process()
    {
        echo PHP_EOL;
        Logger::EchoLog('ip : ' . Config::$node->getHost());
        Logger::EchoLog('address : ' . Config::$node->getAddress());
        Logger::EchoLog('public_key : ' . Config::$node->getPublicKey());
        Logger::EchoLog('private_key : ' . Config::$node->getPrivateKey());
        echo PHP_EOL;
    }
}
