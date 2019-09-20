<?php

namespace src\Util;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use RuntimeException;

class MongoDB
{
    public $manager;
    public $bulk;

    protected $db_host;
    protected $db_port;
    protected $db_name;

    protected $m_db;
    protected $m_namespace;
    protected $m_query;
    protected $m_command;
    private $logger;

    public function __construct()
    {
        $this->logger = Logger::getLogger('MongoDB');

        $this->Init();

        $this->manager = new Manager("mongodb://{$this->db_host}:{$this->db_port}");
        $this->bulk = new BulkWrite();
    }

    public function Init()
    {
        $this->db_host = 'localhost';
        $this->db_port = 27017;
    }

    public function Query($namespace, $query_filter, $query_options = [])
    {
        $this->m_namespace = $namespace;
        $this->m_query = new Query($query_filter, $query_options);

        try {
            return $this->manager->executeQuery($this->m_namespace, $this->m_query);
        } catch (Exception $e) {
            $this->logger->err('Exception database', ['exception' => $e]);

            throw new RuntimeException($e->getMessage());
        }
    }

    public function Command($db, $command_document)
    {
        $this->m_db = $db;
        $this->m_command = new Command($command_document);

        try {
            return $this->manager->executeCommand($this->m_db, $this->m_command);
        } catch (Exception $e) {
            $this->logger->err('Exception database', ['exception' => $e]);

            throw new RuntimeException($e->getMessage());
        }
    }

    public function BulkWrite($namespace, $bulk = null)
    {
        if ($bulk === null) {
            $this->manager->executeBulkWrite($namespace, $this->bulk);
            $this->bulk = new BulkWrite();
        } else {
            $this->manager->executeBulkWrite($namespace, $bulk);
        }
    }
}
