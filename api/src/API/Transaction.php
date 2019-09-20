<?php

namespace src\API;

use src\API;
use src\System\Chunk;
use src\System\Config;
use src\System\Tracker;
use src\System\TransactionManager;
use src\Util\RestCall;

class Transaction extends API
{
    protected $rest;

    protected $transaction_manager;

    protected $transaction;
    protected $public_key;
    protected $signature;

    public function __construct()
    {
        $this->rest = RestCall::GetInstance();

        $this->transaction_manager = new TransactionManager();
    }

    public function _init()
    {
        $this->transaction = json_decode($this->getParam($_REQUEST, 'transaction', ['default' => '{}']), true);
        $this->public_key = $this->getParam($_REQUEST, 'public_key', ['default' => '']);
        $this->signature = $this->getParam($_REQUEST, 'signature', ['default' => '']);
    }

    public function _process()
    {
        $type = $this->getParam($this->transaction, 'type');

        $transaction = $this->transaction;
        $thash = hash('sha256', json_encode($transaction));
        $public_key = $this->public_key;
        $signature = $this->signature;

        $this->transaction_manager->InitializeTransaction($type, $transaction, $thash, $public_key, $signature);
        $validity = $this->transaction_manager->GetTransactionValidity();

        if ($validity == false) {
            $this->Error('Invalid transaction');
        }
    }

    public function _end()
    {
        if (Tracker::IsValidator(Config::$node->getAddress())) {
            $this->AddTransaction();
            $this->data['result'] = 'Transaction is added';
        } else {
            $this->BroadcastTransaction();
            $this->data['result'] = 'Transaction is broadcast';
        }

        $this->data['transaction'] = $this->transaction;
        $this->data['public_key'] = $this->public_key;
        $this->data['signature'] = $this->signature;
    }

    public function AddTransaction()
    {
        Chunk::SaveAPIChunk([
            'transaction' => $this->transaction,
            'public_key' => $this->public_key,
            'signature' => $this->signature,
        ], $this->transaction['timestamp']);
    }

    public function BroadcastTransaction()
    {
        $validator = Tracker::GetRandomValidator();
        $host = $validator['host'];

        $url = "http://{$host}/transaction";
        $data = [
            'transaction' => json_encode($this->transaction),
            'public_key' => $this->public_key,
            'signature' => $this->signature,
        ];

        $this->rest->POST($url, $data);
    }
}
