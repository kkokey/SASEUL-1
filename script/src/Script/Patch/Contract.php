<?php

namespace src\Script\Patch;

use src\Config\MongoDb;
use src\Script;
use src\System\Database;
use src\Util\Logger;

class Contract extends Script
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

        $this->db->Command($this->namespaceCommitted, ['create' => 'contract']);
    }

    public function CreateIndex()
    {
        Logger::EchoLog('Create Index');

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'contract',
            'indexes' => [
                ['key' => ['cid' => 1], 'name' => 'cid_asc'],
                ['key' => ['chash' => 1], 'name' => 'chash_asc'],
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['timestamp' => 1, 'chash' => 1], 'name' => 'timestamp_chash_asc'],
            ]
        ]);
    }
}
