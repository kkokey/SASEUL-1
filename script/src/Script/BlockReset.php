<?php

namespace src\Script;

use src\Config\MongoDb;
use src\Script;
use src\System\Config;
use src\System\Database;
use src\Util\Logger;
use src\Util\RestCall;

class BlockReset extends Script
{
    private $db;
    private $namespacePrecommit;
    private $namespaceCommitted;

    private $rest;

    public function __construct()
    {
        $this->db = Database::GetInstance();

        $this->namespacePrecommit = MongoDb::NAMESPACE_PRECOMMIT;
        $this->namespaceCommitted = MongoDb::NAMESPACE_COMMITTED;

        $this->rest = RestCall::GetInstance();
    }

    public function _process()
    {
        Logger::EchoLog('Reset? [y/n] ');
        $stdin = trim(fgets(STDIN));

        if ($stdin !== 'y') {
            return;
        }

        $this->DeleteFiles();
        $this->DropDatabase();
        $this->CreateDatabase();
        $this->CreateIndex();

        Logger::EchoLog('Success');
    }

    public function DeleteFiles()
    {
        Logger::EchoLog('Delete Files : API Chunk ');
        shell_exec('rm -rf ' . Config::$block->getApiChunks() . '/* ');

        Logger::EchoLog('Delete Files : Broadcast Chunk ');
        shell_exec('rm -rf ' . Config::$block->getBroadcastChunks() . '/* ');

        Logger::EchoLog('Delete Files : Transaction Chunk ');
        shell_exec('rm -rf ' . Config::$block->getTransactions() . '/* ');
    }

    public function DropDatabase()
    {
        Logger::EchoLog('Drop Database');

        $this->db->Command($this->namespacePrecommit, ['dropDatabase' => 1]);
        $this->db->Command($this->namespaceCommitted, ['dropDatabase' => 1]);
    }

    public function CreateDatabase()
    {
        Logger::EchoLog('Create Database');

        $this->db->Command($this->namespacePrecommit, ['create' => 'transactions']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'transactions']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'blocks']);

        $this->db->Command($this->namespaceCommitted, ['create' => 'coin']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'attributes']);
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
    }
}
