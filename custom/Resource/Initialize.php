<?php

namespace src\Resource;

use src\Config\MongoDb;
use src\System\Config;
use src\System\Database;
use src\System\Key;
use src\System\Rank;
use src\System\Resource;
use src\Util\Host;
use src\Util\Logger;

class Initialize extends Resource
{
    private $logger;

    private $db;

    private $namespacePrecommit;
    private $namespaceCommitted;
    private $namespaceTracker;

    private $request;
    private $thash;
    private $public_key;
    private $signature;

    private $type;
    private $from;
    private $timestamp;

    public function __construct()
    {
        $this->logger = Logger::getLogger('custom');

        $this->db = Database::GetInstance();

        $this->namespacePrecommit = MongoDb::NAMESPACE_PRECOMMIT;
        $this->namespaceCommitted = MongoDb::NAMESPACE_COMMITTED;
        $this->namespaceTracker = MongoDb::NAMESPACE_TRACKER;
    }

    public function initialize(array $request, string $thash, string $public_key, string $signature): void
    {
        $this->request = $request;
        $this->thash = $thash;
        $this->public_key = $public_key;
        $this->signature = $signature;

        $this->type = $this->request['type'] ?? '';
        $this->from = $this->request['from'] ?? '';
        $this->timestamp = $this->request['timestamp'] ?? 0;
    }

    public function process(): void
    {
        $this->createDatabase();
        $this->createIndex();
        $this->createGenesisTracker();
    }

    public function getValidity(): bool
    {
        $validity = !empty($this->timestamp)
            && $this->type === substr(strrchr(__CLASS__, '\\'), 1)
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature)
            && Host::isValidAddress($this->from)
            && $this->checkCleanDatabase()
        ;

        $this->logger->debug('validity', [$validity]);

        return $validity;
    }

    public function getResponse(): array
    {
        return ['status' => 'success'];
    }

    public function checkCleanDatabase(): bool
    {
        $cursor = $this->db->Query(
            "{$this->namespaceTracker}.tracker",
            ['address' => Config::$genesis->getAddress()]
        );

        $trackerList = $cursor->toArray();
        $this->logger->debug(
            'check Query',
            ['data' => $trackerList, 'address' => Config::$genesis->getAddress()]
        );

        if (!empty($trackerList)) {
            return false;
        }

        return true;
    }

    public function createDatabase(): void
    {
        $this->createPrecommitDatabase();
        $this->createCommittedDatabase();
        $this->createTrackerDatabase();

        $this->logger->info('Created Database');
    }

    public function createIndex(): void
    {
        $this->createPrecommitIndex();
        $this->createCommittedIndex();
        $this->createTrackerIndex();

        $this->logger->info('Created Index');
    }

    public function createGenesisTracker(): void
    {
        $this->db->bulk->insert([
            'host' => Config::$genesis->getHost(),
            'address' => Config::$genesis->getAddress(),
            'rank' => Rank::VALIDATOR,
            'status' => 'admitted'
        ]);

        $this->db->BulkWrite("{$this->namespaceTracker}.tracker");

        $this->logger->info('Created genesis tracker');
    }

    private function createPrecommitDatabase(): void
    {
        $this->db->Command($this->namespacePrecommit, ['create' => 'transactions']);
    }

    private function createCommittedDatabase(): void
    {
        $this->db->Command($this->namespaceCommitted, ['create' => 'transactions']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'blocks']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'coin']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'attributes']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'contract']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'exchange_order']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'exchange_result']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'token']);
        $this->db->Command($this->namespaceCommitted, ['create' => 'token_list']);
    }

    private function createTrackerDatabase(): void
    {
        $this->db->Command($this->namespaceTracker, ['create' => 'tracker']);
    }

    private function createPrecommitIndex(): void
    {
        $this->db->Command($this->namespacePrecommit, [
            'createIndexes' => 'transactions',
            'indexes' => [
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['timestamp' => 1, 'thash' => 1], 'name' => 'timestamp_thash_asc'],
                ['key' => ['thash' => 1, 'timestamp' => 1], 'name' => 'thash_timestamp_unique', 'unique' => 1]
            ]
        ]);
    }

    private function createCommittedIndex(): void
    {
        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'transactions',
            'indexes' => [
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['timestamp' => 1, 'thash' => 1], 'name' => 'timestamp_thash_asc'],
                ['key' => ['thash' => 1, 'timestamp' => 1], 'name' => 'thash_timestamp_unique', 'unique' => 1]
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'blocks',
            'indexes' => [
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['block_number' => 1], 'name' => 'block_number_asc']
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'coin',
            'indexes' => [
                ['key' => ['address' => 1], 'name' => 'address_unique', 'unique' => 1]
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'attributes',
            'indexes' => [
                ['key' => ['address' => 1, 'key' => 1], 'name' => 'address_unique', 'unique' => 1]
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'contract',
            'indexes' => [
                ['key' => ['cid' => 1], 'name' => 'cid_asc'],
                ['key' => ['chash' => 1], 'name' => 'chash_asc'],
                ['key' => ['timestamp' => 1], 'name' => 'timestamp_asc'],
                ['key' => ['timestamp' => -1], 'name' => 'timestamp_desc'],
                ['key' => ['timestamp' => 1, 'chash' => 1], 'name' => 'timestamp_chash_asc']
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'exchange_order',
            'indexes' => [
                ['key' => ['eid' => 1], 'name' => 'eid_asc', 'unique' => 1],
                [
                    'key' => ['from_type' => 1, 'from_currency_name' => 1, 'to_type' => 1, 'to_currency_name' => 1],
                    'name' => 'type_asc'
                ],
                ['key' => ['from' => 1], 'name' => 'from_asc'],
                ['key' => ['to' => 1], 'name' => 'to_asc']
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'exchange_result',
            'indexes' => [
                ['key' => ['from' => 1], 'name' => 'from_asc'],
                ['key' => ['to' => 1], 'name' => 'to_asc']
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'token',
            'indexes' => [
                ['key' => ['address' => 1], 'name' => 'address_asc'],
                ['key' => ['token_name' => 1], 'name' => 'token_name_asc'],
                ['key' => ['address' => 1, 'token_name' => 1], 'name' => 'address_token_name_asc', 'unique' => 1]
            ]
        ]);

        $this->db->Command($this->namespaceCommitted, [
            'createIndexes' => 'token_list',
            'indexes' => [
                ['key' => ['token_name' => 1], 'name' => 'token_name_asc', 'unique' => 1]
            ]
        ]);
    }

    private function createTrackerIndex(): void
    {
        $this->db->Command($this->namespaceTracker, [
            'createIndexes' => 'tracker',
            'indexes' => [
                ['key' => ['address' => 1], 'name' => 'address_unique', 'unique' => 1]
            ]
        ]);
    }
}
