<?php

namespace src\Util;

use src\System\Config;

class Host
{
    public static function isValid(string $host): bool
    {
        $match_pattern = '/^([\w\-]+\\.){1,3}[\w\-]+$/';
        $not_match_pattern = '/^[\W]+/';

        return preg_match($match_pattern, $host) && !preg_match($not_match_pattern, $host);
    }

    public static function isValidAddress(string $address): bool
    {
        return Config::$node->getAddress() === $address;
    }
}
