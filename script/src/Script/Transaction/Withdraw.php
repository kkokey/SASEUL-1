<?php

namespace src\Script\Transaction;

use src\Script;
use src\System\Config;
use src\System\Key;
use src\System\Tracker;
use src\Util\DateTime;
use src\Util\Logger;
use src\Util\RestCall;

class Withdraw extends Script
{
    private $rest;

    private $m_result;

    public function __construct()
    {
        $this->rest = RestCall::GetInstance();
    }

    public function _process()
    {
        Logger::EchoLog('Type amount to withdraw coin. ');
        $amount = trim(fgets(STDIN));

        $validator = Tracker::GetRandomValidator();
        $host = $validator['host'];

        $transaction = [
            'type' => 'Withdraw',
            'version' => Config::VERSION,
            'from' => Config::$node->getAddress(),
            'amount' => $amount,
            'fee' => 0,
            'transactional_data' => '',
            'timestamp' => DateTime::Microtime(),
        ];

        $thash = hash('sha256', json_encode($transaction));
        $public_key = Config::$node->getPublicKey();
        $signature = Key::makeSignature($thash, Config::$node->getPrivateKey(), Config::$node->getPublicKey());

        $url = "http://{$host}/transaction";
        $ssl = false;
        $data = [
            'transaction' => json_encode($transaction),
            'public_key' => $public_key,
            'signature' => $signature,
        ];
        $header = [];

        $result = $this->rest->POST($url, $data, $ssl, $header);
        $this->m_result = json_decode($result, true);
    }

    public function _end()
    {
        $this->data['result'] = $this->m_result;
    }
}
