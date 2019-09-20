<?php

namespace src\Util;

class Merkle
{
    public static function MakeMerkleHash($array)
    {
        if (count($array) === 0) {
            return hash('sha256', json_encode($array));
        }

        $hash_array = [];

        foreach ($array as $item) {
            $hash_array[] = self::Hash($item);
        }

        while (count($hash_array) > 1) {
            $tmp_array = $hash_array;
            $hash_array = [];

            for ($i = 0; $i < count($tmp_array); $i = $i + 2) {
                if ($i === count($tmp_array) - 1) {
                    $hash_array[] = $tmp_array[$i];
                } else {
                    $hash_array[] = self::Hash($tmp_array[$i] . $tmp_array[$i + 1]);
                }
            }
        }

        return $hash_array[0];
    }

    public static function Hash($obj)
    {
        if (in_array(gettype($obj), ['array', 'object', 'resource'])) {
            return hash('sha256', json_encode($obj));
        }

        return hash('sha256', $obj);
    }

    public static function MakeBlockHash($last_blockhash, $transaction_hash)
    {
        return hash('sha256', $last_blockhash . $transaction_hash);
    }
}
