<?php

namespace src\Util;

class TypeChecker
{
    public static function StructureCheck($tpl, $value)
    {
        foreach ($tpl as $k => $v) {
            if (!isset($value[$k])) {
                return false;
            }
            if (gettype($v) !== gettype($value[$k])) {
                return false;
            }
            if (is_array($v) && count($v) > 0 && self::StructureCheck($v, $value[$k]) === false) {
                return false;
            }
        }

        return true;
    }
}
