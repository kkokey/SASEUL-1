<?php

namespace src\Config;

use src\Util\Config;

class Node
{
    private $host;
    private $address;
    private $publicKey;
    private $privateKey;

    public function __construct()
    {
        $this->host = Config::getFromEnv('NODE_HOST');
        $this->address = Config::getFromEnv('NODE_ADDRESS');
        $this->publicKey = Config::getFromEnv('NODE_PUBLIC_KEY');
        $this->privateKey = Config::getFromEnv('NODE_PRIVATE_KEY');
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getHost()
    {
        return $this->host;
    }
}
