<?php

namespace src\Script;

use src\Config\MongoDb;
use src\Script;
use src\System\Cache;
use src\System\Config;
use src\System\Database;
use src\System\Rank;
use src\Util\Logger;

class Reset extends Script
{
    private $db;
    private $cache;
    private $namespacePrecommit;
    private $namespaceCommitted;
    private $namespaceTracker;

    private $patch_contract;
    private $patch_exchange;
    private $patch_token;

    public function __construct()
    {
        $this->db = Database::GetInstance();
        $this->cache = Cache::GetInstance();

        $this->namespacePrecommit = MongoDb::NAMESPACE_PRECOMMIT;
        $this->namespaceCommitted = MongoDb::NAMESPACE_COMMITTED;
        $this->namespaceTracker = MongoDb::NAMESPACE_TRACKER;

        $this->patch_contract = new Script\Patch\Contract();
        $this->patch_exchange = new Script\Patch\Exchange();
        $this->patch_token = new Script\Patch\Token();
    }

    public function _process()
    {
        Logger::EchoLog('Reset? [y/n] ');
        $stdin = trim(fgets(STDIN));

        if ($stdin !== 'y') {
            return;
        }

        $this->DeleteFiles();
        $this->FlushCache();
        $this->DropDatabase();
        $this->CreateDatabase();
        $this->CreateIndex();
        $this->CreateGenesisTracker();
        $this->patch_contract->Exec();
        $this->patch_exchange->Exec();
        $this->patch_token->Exec();

        Logger::EchoLog('Success');
    }

    public function DeleteFiles()
    {
        Logger::EchoLog('Delete Files : API Chunk ');
        shell_exec('rm -rf ' . Config::$block->getApiChunks());
        shell_exec('mkdir ' . Config::$block->getApiChunks());
        shell_exec('chmod g+w ' . Config::$block->getApiChunks());

        Logger::EchoLog('Delete Files : Broadcast Chunk ');
        shell_exec('rm -rf ' . Config::$block->getBroadcastChunks());
        shell_exec('mkdir ' . Config::$block->getBroadcastChunks());
        shell_exec('chmod g+w ' . Config::$block->getBroadcastChunks());

        Logger::EchoLog('Delete Files : Transaction Chunk ');
        shell_exec('rm -rf ' . Config::$block->getTransactions());
        shell_exec('mkdir ' . Config::$block->getTransactions());
        shell_exec('chmod g+w ' . Config::$block->getTransactions());
    }

    public function FlushCache()
    {
        $this->cache->flush();
    }

    public function DropDatabase()
    {
        Logger::EchoLog('Drop Database');

        $this->db->Command($this->namespacePrecommit, ['dropDatabase' => 1]);
        $this->db->Command($this->namespaceCommitted, ['dropDatabase' => 1]);
        $this->db->Command($this->namespaceTracker, ['dropDatabase' => 1]);
    }

    public function CreateDatabase()
    {
        Logger::EchoLog('Create Database');

        $this->db->Command($this->namespacePrecommit, ['create' => 'transactions']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'transactions']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'blocks']);

        $this->db->Command($this->namespaceCommitted, ['create' => 'coin']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'attributes']);

        $this->db->Command($this->namespaceTracker, ['create' => 'tracker']);
    }

    public function CreateIndex()
    {
        Logger::EchoLog('Create Index');

        $this->db->Command($this->namespacePrecommit, [
            'createIndexes' => 'transactions',
            'indexes' => [
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['timestamp' => 1, 'thash' => 1], 'name' => 'timestamp_thash_asc'],
                ['key' => ['thash' => 1, 'timestamp' => 1], 'name' => 'thash_timestamp_unique', 'unique' => 1],
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'transactions',
            'indexes' => [
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['timestamp' => 1, 'thash' => 1], 'name' => 'timestamp_thash_asc'],
                ['key' => ['thash' => 1, 'timestamp' => 1], 'name' => 'thash_timestamp_unique', 'unique' => 1],
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'blocks',
            'indexes' => [
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['block_number' => 1], 'name' => 'block_number_asc'],
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'coin',
            'indexes' => [
                ['key' => ['address' => 1], 'name' => 'address_unique', 'unique' => 1],
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'attributes',
            'indexes' => [
                ['key' => ['address' => 1, 'key' => 1], 'name' => 'address_unique', 'unique' => 1],
            ]
        ]);

        $this->db->Command($this->namespaceTracker, [
            'createIndexes' => 'tracker',
            'indexes' => [
                ['key' => ['address' => 1], 'name' => 'address_unique', 'unique' => 1],
            ]
        ]);
    }

    public function CreateGenesisTracker()
    {
        Logger::EchoLog('CreateGenesisTracker');

        $this->db->bulk->insert([
            'host' => Config::$genesis->getHost(),
            'address' => Config::$genesis->getAddress(),
            'rank' => Rank::VALIDATOR,
            'status' => 'admitted',
        ]);

        $this->db->BulkWrite($this->namespaceTracker . '.tracker');
    }
}
