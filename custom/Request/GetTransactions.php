<?php

namespace src\Request;

use src\System\Block;
use src\System\Config;
use src\System\Key;
use src\System\Request;
use src\Util\Logger;

class GetTransactions extends Request
{
    private $logger;

    private $reqeust;
    private $thash;
    private $public_key;
    private $signature;

    private $type;
    private $version;
    private $from;
    private $limit;
    private $skip;
    private $transactional_data;
    private $timestamp;

    public function __construct()
    {
        $this->logger = Logger::getLogger('Request');
    }

    public function initialize(array $request, string $thash, string $public_key, string $signature): void
    {
        $this->reqeust = $request;
        $this->thash = $thash;
        $this->public_key = $public_key;
        $this->signature = $signature;

        $this->type = $this->reqeust['type'] ?? '';
        $this->version = $this->reqeust['version'] ?? '';
        $this->from = $this->reqeust['from'] ?? '';
        $this->limit = $this->reqeust['limit'] ?? 100;
        $this->skip = $this->reqeust['skip'] ?? 0;
        $this->transactional_data = $this->reqeust['transactional_data'] ?? '';
        $this->timestamp = $this->reqeust['timestamp'] ?? 0;
    }

    public function getValidity(): bool
    {
        $validity = !empty($this->timestamp)
            && $this->type === substr(strrchr(__CLASS__, '\\'), 1)
            && $this->version === Config::VERSION
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature);

        $this->logger->debug('GetTransactions validity', [$validity]);

        return $validity;
    }

    public function getResponse(): array
    {
        $block_transactions = Block::GetTransactions($this->limit, $this->skip, $this->public_key, $this->from);

        $this->logger->debug('GetTransactions response data', [$block_transactions]);

        return $block_transactions;
    }
}
