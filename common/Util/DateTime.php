<?php

namespace src\Util;

class DateTime
{
    public static function Microtime()
    {
        return (int) (array_sum(explode(' ', microtime())) * 1000000);
    }

    public static function MicrotimeWithComma()
    {
        return array_sum(explode(' ', microtime()));
    }

    public static function Millitime()
    {
        return (int) (array_sum(explode(' ', microtime())) * 1000);
    }

    public static function Date()
    {
        return date('YmdHis');
    }
}
