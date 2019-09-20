<?php

namespace src\Util;

class ScriptFinder
{
    public static function GetStatusInterfaces($opt = true)
    {
        return self::GetInterfaces(ROOT_DIR . '/../custom/Status', $opt);
    }

    public static function GetTransactionInterfaces($opt = true)
    {
        return self::GetInterfaces(ROOT_DIR . '/../custom/Transaction', $opt);
    }

    public static function GetRequestInterfaces($opt = true)
    {
        return self::GetInterfaces(ROOT_DIR . '/../custom/Request', $opt);
    }

    public static function GetVRequestInterfaces($opt = true)
    {
        return self::GetInterfaces(ROOT_DIR . '/src/VRequest', $opt);
    }

    public static function GetInterfaces($dir, $opt = true)
    {
        $scripts = self::GetFiles($dir, $dir);

        if ($opt === true) {
            $scripts = preg_replace('/\.php$/', '', $scripts);
        }

        return $scripts;
    }

    public static function GetFiles($full_dir, $del_dir = '')
    {
        $d = scandir($full_dir);
        $files = [];

        foreach ($d as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            if (is_dir($full_dir . '/' . $dir)) {
                $c_scripts = self::GetFiles($full_dir . '/' . $dir, $del_dir);
                $files = array_merge($files, $c_scripts);
            } else {
                $escaped_del_dir = preg_replace('/\//', '\\\/', $del_dir);
                $files[] = preg_replace('/^' . $escaped_del_dir . '\//', '', $full_dir . '/' . $dir);
            }
        }

        return $files;
    }
}
