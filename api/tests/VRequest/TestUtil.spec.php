<?php

use src\System\Key;

function createAccount($msg, $privateKey, $publicKey)
{
    $hashed_msg = hash('sha256', json_encode($msg));

    return [
        'hashedMsg' => $hashed_msg,
        'privateKey' => $privateKey,
        'publicKey' => $publicKey,
        'signature' => Key::makeSignature($hashed_msg, $privateKey, $publicKey),
        'address' => Key::makeAddress($publicKey),
    ];
}
