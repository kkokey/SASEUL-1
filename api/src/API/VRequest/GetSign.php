<?php

namespace src\API\VRequest;

use src\API;
use src\System\Config;
use src\System\Key;

class GetSign extends API
{
    private $string;

    public function _init()
    {
        $pram_string = $this->getParam($_REQUEST, 'string', ['type' => 'string']);

        if (mb_strlen($pram_string) != 32) {
            $this->Error('Length of string must be 32. ');
        }

        $this->string = $pram_string;
    }

    public function _process()
    {
        $public_key = Config::$node->getPublicKey();
        $address = Config::$node->getAddress();
        $signature = Key::makeSignature($this->string, Config::$node->getPrivateKey(), Config::$node->getPublicKey());

        $this->data = [
            'string' => $this->string,
            'public_key' => $public_key,
            'address' => $address,
            'signature' => $signature,
        ];
    }
}
