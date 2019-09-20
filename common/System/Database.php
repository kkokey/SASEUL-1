<?php

namespace src\System;

use src\Config\MongoDb as MongoDbConfig;
use src\Util\MongoDB;

class Database extends MongoDB
{
    protected static $instance = null;

    public function Init()
    {
        $this->db_host = MongoDbConfig::HOST;
        $this->db_port = MongoDbConfig::PORT;
    }

    public static function GetInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
