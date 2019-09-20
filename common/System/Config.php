<?php

namespace src\System;

use src\Config\Block;
use src\Config\Fee;
use src\Config\Genesis;
use src\Config\Node;

class Config
{
    public const VERSION = '0.5';

    public const ADDRESS_PREFIX = ['0x00', '0x6f'];

    public const TESTABLE = true;

    // TODO: Easier ways to find ways to auto-complete in IDEA.
    public static $block;
    public static $genesis;
    public static $node;
    public static $fee;

    public static $log_path;
    public static $log_level;

    public static function init(): void
    {
        self::$block = new Block();
        self::$genesis = new Genesis();
        self::$node = new Node();
        self::$fee = new Fee();

        static::$log_path = \src\Util\Config::getFromEnv('LOG_PATH');
        static::$log_level = \src\Util\Config::getFromEnv('LOG_LEVEL');
    }
}
