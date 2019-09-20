<?php

namespace src\Script\Patch;

use src\Config\MongoDb;
use src\Script;
use src\System\Database;
use src\Util\Logger;

class Token extends Script
{
    private $db;
    private $namespaceCommitted;

    public function __construct()
    {
        $this->db = Database::GetInstance();

        $this->namespaceCommitted = MongoDb::NAMESPACE_COMMITTED;
    }

    public function _process()
    {
        $this->CreateDatabase();
        $this->CreateIndex();
    }

    public function CreateDatabase()
    {
        Logger::EchoLog('Create Database');

        $this->db->Command($this->namespaceCommitted, ['create' => 'token']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'token_list']);
    }

    public function CreateIndex()
    {
        Logger::EchoLog('Create Index');

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'token',
            'indexes' => [
                ['key' => ['address' => 1], 'name' => 'address_asc'],
                ['key' => ['token_name' => 1], 'name' => 'token_name_asc'],
                ['key' => ['address' => 1, 'token_name' => 1], 'name' => 'address_token_name_asc', 'unique' => 1],
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'token_list',
            'indexes' => [
                ['key' => ['token_name' => 1], 'name' => 'token_name_asc', 'unique' => 1],
            ]
        ]);
    }
}
