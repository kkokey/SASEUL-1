<?php

namespace src\Script\Request;

use src\Script;
use src\System\Config;
use src\System\Key;
use src\System\Tracker;
use src\Util\DateTime;
use src\Util\RestCall;

class GetRole extends Script
{
    private $rest;

    public function __construct()
    {
        $this->rest = RestCall::GetInstance();
    }

    public function _process()
    {
        $validator = Tracker::GetRandomValidator();
        $host = $validator['host'];
        $address = Config::$node->getAddress();

        $request = [
            'type' => 'GetRole',
            'version' => Config::VERSION,
            'from' => $address,
            'transactional_data' => '',
            'timestamp' => DateTime::Microtime(),
        ];

        $thash = hash('sha256', json_encode($request));
        $public_key = Config::$node->getPublicKey();
        $signature = Key::makeSignature(
            $thash,
            Config::$node->getPrivateKey(),
            Config::$node->getPublicKey()
        );

        $url = "http://{$host}/request";
        $ssl = false;
        $data = [
            'request' => json_encode($request),
            'public_key' => $public_key,
            'signature' => $signature,
        ];
        $header = [];

        $result = $this->rest->POST($url, $data, $ssl, $header);
        $result = json_decode($result, true);

        $data = $result['data'];

        echo PHP_EOL;
        echo '[Request Info]' . PHP_EOL;
        echo 'Validator address : ' . $validator['address'] . PHP_EOL;
        echo 'Validator host : ' . $validator['host'] . PHP_EOL;
        echo PHP_EOL;
        echo '[Balance Info]' . PHP_EOL;
        echo 'My address : ' . $address . PHP_EOL;
        echo 'Role : ' . $data['role'] . PHP_EOL;
        echo PHP_EOL;
    }
}
