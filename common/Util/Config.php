<?php

namespace src\Util;

class Config
{
    public static function getFromEnv(string $key)
    {
        $value = getenv($key);

        if (empty($value)) {
            echo "Environment variables failed assertions: {$key} is messing.\n";

            return false;
        }

        return $value;
    }
}
