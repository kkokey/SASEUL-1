<?php

namespace src\Script\Test;

use src\Script;
use src\System\Config;
use src\System\Key;
use src\System\Tracker;
use src\Util\DateTime;
use src\Util\RestCall;

class SendCoin extends Script
{
    private $rest;

    private $validators;
    private $m_host;
    private $m_to_addresses;

    public function __construct()
    {
        $this->rest = RestCall::GetInstance();
    }

    public function _process()
    {
        $this->SetToAddresses();
        $this->SetHost();

        for ($i = 0; $i < 100; $i++) {
            foreach ($this->m_to_addresses as $address) {
                $this->SendCoin($address, 120000);
            }
        }
    }

    public function SetToAddresses()
    {
        $this->m_to_addresses = [];

        for ($i = 0; $i < 100; $i++) {
            $pv = Key::makePrivateKey();
            $pub = Key::makePublicKey($pv);
            $addr = Key::makeAddress($pub);

            $this->m_to_addresses[] = $addr;
        }
    }

    public function SetHost()
    {
        $this->validators = Tracker::GetValidator();
    }

    public function SendCoin($to, $amount)
    {
        $count = count($this->validators);
        $pick = rand(0, $count - 1);

        $host = $this->validators[$pick]['host'];

        $transaction = [
            'type' => 'SendCoin',
            'version' => Config::VERSION,
            'from' => Config::$node->getAddress(),
            'to' => $to,
            'amount' => $amount,
            'fee' => (int) ($amount * Config::$fee->getRate()),
            'transactional_data' => '',
            'timestamp' => DateTime::Microtime(),
        ];

        $thash = hash('sha256', json_encode($transaction));
        $public_key = Config::$node->getPublicKey();
        $signature = Key::makeSignature(
            $thash,
            Config::$node->getPrivateKey(),
            Config::$node->getPublicKey()
        );

        $url = "http://{$host}/transaction";
        $ssl = false;
        $data = [
            'transaction' => json_encode($transaction),
            'public_key' => $public_key,
            'signature' => $signature,
        ];
        $header = [];

        $this->rest->POST($url, $data, $ssl, $header);
    }
}
