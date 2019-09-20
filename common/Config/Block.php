<?php

namespace src\Config;

use src\Util\Config;

class Block
{
    public const CHUNK_PREFIX = 'chunks_';

    private $apiChunks;
    private $broadcastChunks;
    private $transactions;

    private $microIntervalOfChunk;

    public function __construct()
    {
        $this->microIntervalOfChunk = Config::getFromEnv('MICRO_INTERVAL_CHUNK');

        $rootPath = __DIR__ . '/../../';
        $dirBlockchain = $rootPath . 'blockchain/';
        $this->apiChunks = $dirBlockchain . 'apichunks';
        $this->broadcastChunks = $dirBlockchain . 'broadcastchunks';
        $this->transactions = $dirBlockchain . 'transactions';
    }

    public function getApiChunks()
    {
        return $this->apiChunks;
    }

    public function getBroadcastChunks()
    {
        return $this->broadcastChunks;
    }

    public function getTransactions()
    {
        return $this->transactions;
    }

    public function getMicroIntervalOfChunk()
    {
        return $this->microIntervalOfChunk;
    }
}
