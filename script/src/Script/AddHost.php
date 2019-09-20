<?php

namespace src\Script;

use src\Script;
use src\System\Config;
use src\System\Key;
use src\System\Tracker;
use src\Util\DateTime;
use src\Util\RestCall;

class AddHost extends Script
{
    private $rest;
    private $sync_tracker;

    private $m_result;

    public function __construct()
    {
        $this->rest = RestCall::GetInstance();

        $this->sync_tracker = new SyncTracker();
    }

    public function _process()
    {
        $fullnodes = Tracker::GetFullNode();

        $request = [
            'type' => 'AddTracker',
            'version' => Config::VERSION,
            'from' => Config::$node->getAddress(),
            'host' => Config::$node->getHost(),
            'transactional_data' => '',
            'timestamp' => DateTime::Microtime(),
        ];

        $thash = hash('sha256', json_encode($request));
        $publicKey = Config::$node->getPublicKey();
        $signature = Key::makeSignature(
            $thash,
            Config::$node->getPrivateKey(),
            Config::$node->getPublicKey()
        );

        $ssl = false;
        $data = [
            'request' => json_encode($request),
            'public_key' => $publicKey,
            'signature' => $signature,
        ];
        $header = [];

        foreach ($fullnodes as $node) {
            if (empty($node['host'])) {
                continue;
            }

            $host = $node['host'];
            $url = "http://{$host}/request";
            $result = $this->rest->POST($url, $data, $ssl, $header);
            $this->m_result[] = json_decode($result, true);
        }
    }

    public function _end()
    {
        $this->sync_tracker->Exec();
    }
}
