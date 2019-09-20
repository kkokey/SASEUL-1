<?php

namespace src\System;

use src\Config\Memcached as MemcachedConfig;
use src\Util\Memcached;

class Cache extends Memcached
{
    protected static $instance = null;

    public function initialize()
    {
        $this->prefix = MemcachedConfig::PREFIX;
        $this->host = MemcachedConfig::HOST;
        $this->port = MemcachedConfig::PORT;
    }

    public static function GetInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
