<?php

namespace src\System;

use src\Util\Ed25519;

class Key
{
    public const ADDRESS_SIZE = 48;
    public const PUBLIC_KEY_SIZE = 64;
    public const PRIVATE_KEY_SIZE = 64;

    public static function makePrivateKey()
    {
        return Ed25519::MakePrivateKey();
    }

    public static function makePublicKey($private_key)
    {
        return Ed25519::MakePublicKey($private_key);
    }

    public static function makeAddress($public_key)
    {
        return Ed25519::MakeAddress($public_key, Config::ADDRESS_PREFIX[0], Config::ADDRESS_PREFIX[1]);
    }

    public static function makeSignature($str, $private_key, $public_key)
    {
        return Ed25519::MakeSignature($str, $private_key, $public_key);
    }

    public static function isValidSignature($str, $public_key, $signature)
    {
        return Ed25519::ValidSignature($str, $public_key, $signature);
    }

    public static function isValidAddress($address, $public_key): bool
    {
        return !empty($address)
            && (mb_strlen($address) === self::ADDRESS_SIZE)
            && (self::makeAddress($public_key) === $address);
    }
}
