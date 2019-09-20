<?php

namespace src\Script;

use src\Config\MongoDb;
use src\Script;
use src\System\Cache;
use src\System\Config;
use src\System\Database;
use src\System\Key;
use src\Util\DateTime;
use src\Util\Logger;

class Genesis extends Script
{
    private $db;
    private $cache;
    private $namespaceCommitted;

    private $m_stdin;

    public function __construct()
    {
        $this->db = Database::GetInstance();
        $this->cache = Cache::GetInstance();

        $this->namespaceCommitted = MongoDb::NAMESPACE_COMMITTED;
    }

    public function _process()
    {
        $this->CheckYes();

        $this->CheckGenesis();
        $this->CreateKey();
        $this->CreateGenesisTransaction();

        Logger::EchoLog('Success ');
    }

    public function CheckYes()
    {
        Logger::EchoLog('Genesis? [y/n] ');
        $this->m_stdin = trim(fgets(STDIN));

        if ($this->m_stdin !== 'y') {
            exit();
        }
    }

    public function CheckGenesis()
    {
        Logger::EchoLog('CheckGenesis');
        $v = $this->cache->get('CheckGenesis');

        if ($v === false) {
            $this->cache->set('CheckGenesis', 'inProcess', 15);
        } else {
            $this->Error('There is genesis block already ');
        }

        $rs = $this->db->Command($this->namespaceCommitted, ['count' => 'blocks']);

        $count = 0;

        foreach ($rs as $item) {
            $count = $item->n;

            break;
        }

        if ($count > 0) {
            $this->Error('There is genesis block already ');
        }
    }

    public function CreateKey()
    {
        Logger::EchoLog('CreateKey');

        $this->data['node_key'] = [
            'private_key' => Config::$node->getPrivateKey(),
            'public_key' => Config::$node->getPublicKey(),
            'address' => Config::$node->getAddress()
        ];
    }

    public function CreateGenesisTransaction()
    {
        Logger::EchoLog('CreateGenesisTransaction');
        $transaction_genesis = [
            'version' => Config::VERSION,
            'type' => 'Genesis',
            'from' => Config::$node->getAddress(),
            'amount' => Config::$genesis->getCoinValue(),
            'transactional_data' => Config::$genesis->getKey(),
            'timestamp' => DateTime::Microtime(),
        ];

        $transaction_deposit = [
            'version' => Config::VERSION,
            'type' => 'Deposit',
            'from' => Config::$node->getAddress(),
            'amount' => Config::$genesis->getDepositValue(),
            'fee' => 0,
            'transactional_data' => 'Genesis Deposit',
            'timestamp' => DateTime::Microtime(),
        ];

        $thash_genesis = hash('sha256', json_encode($transaction_genesis));
        $public_key_genesis = Config::$node->getPublicKey();
        $signature_genesis = Key::makeSignature(
            $thash_genesis,
            Config::$node->getPrivateKey(),
            Config::$node->getPublicKey()
        );

        $this->AddAPIChunk([
            'transaction' => $transaction_genesis,
            'public_key' => $public_key_genesis,
            'signature' => $signature_genesis,
        ], $transaction_genesis['timestamp']);

        $thash_deposit = hash('sha256', json_encode($transaction_deposit));
        $public_key_deposit = Config::$node->getPublicKey();
        $signature_deposit = Key::makeSignature(
            $thash_deposit,
            Config::$node->getPrivateKey(),
            Config::$node->getPublicKey()
        );

        $this->AddAPIChunk([
            'transaction' => $transaction_deposit,
            'public_key' => $public_key_deposit,
            'signature' => $signature_deposit,
        ], $transaction_deposit['timestamp']);
    }

    public function AddAPIChunk($transaction, $timestamp)
    {
        $filename = Config::$block->getApiChunks() . '/'
            . Config::$block::CHUNK_PREFIX . $this->GetID($timestamp) . '.json';

        $file = fopen($filename, 'a');
        fwrite($file, json_encode($transaction) . ",\n");
        fclose($file);
    }

    public function GetID($timestamp)
    {
        $tid = $timestamp - ($timestamp % Config::$block->getMicroIntervalOfChunk())
            + Config::$block->getMicroIntervalOfChunk();

        return preg_replace('/0{6}$/', '', $tid);
    }
}
