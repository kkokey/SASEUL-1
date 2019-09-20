<?php

namespace src\Script;

use src\Script;
use src\System\Config;
use src\System\Key;
use src\Util\DateTime;
use src\Util\Logger;
use src\Util\RestCall;

class Initialize extends Script
{
    private $logger;

    private $rest;
    private $node;

    public function __construct()
    {
        $this->logger = Logger::getConsoleLogger('script');

        $this->rest = RestCall::GetInstance();
        $this->node = Config::$node;
    }

    public function _process()
    {
        $this->logger->info('Initialize?', ['y/N']);
        $stdin = trim(fgets(STDIN));

        if ($stdin !== 'y') {
            return;
        }

        $host = $this->node->getHost();
        $address = $this->node->getAddress();

        $request = [
            'type' => 'Initialize',
            'from' => $address,
            'timestamp' => DateTime::Microtime()
        ];

        $thash = hash('sha256', json_encode($request));
        $public_key = $this->node->getPublicKey();
        $signature = Key::makeSignature(
            $thash,
            $this->node->getPrivateKey(),
            $this->node->getPublicKey()
        );

        $url = "http://{$host}/resource";
        $ssl = false;
        $data = [
            'resource' => json_encode($request),
            'public_key' => $public_key,
            'signature' => $signature
        ];
        $header = [];

        $result = $this->rest->POST($url, $data, $ssl, $header);
        $result = json_decode($result, true);

        $this->logger->debug('result', [$result]);

        if ($result['status'] === 'fail') {
            $this->logger->info('Fail');
        } else {
            $this->logger->info('Success');
        }
    }
}
