<?php

namespace src\Request;

use src\System\Block;
use src\System\Config;
use src\System\Key;
use src\System\Request;
use src\Util\Logger;

class GetTransaction extends Request
{
    private $logger;

    private $request;
    private $thash;
    private $public_key;
    private $signature;

    private $type;
    private $version;
    private $from;
    private $find_thash;
    private $transactional_data;
    private $timestamp;

    public function __construct()
    {
        $this->logger = Logger::getLogger('Request');
    }

    public function initialize(array $request, string $thash, string $public_key, string $signature): void
    {
        $this->request = $request;
        $this->thash = $thash;
        $this->public_key = $public_key;
        $this->signature = $signature;

        $this->type = $this->request['type'] ?? '';
        $this->version = $this->request['version'] ?? '';
        $this->from = $this->request['from'] ?? '';
        $this->find_thash = $this->request['find_thash'] ?? '';
        $this->transactional_data = $this->request['transactional_data'] ?? '';
        $this->timestamp = $this->request['timestamp'] ?? 0;
    }

    public function getValidity(): bool
    {
        $validity = !empty($this->timestamp)
            && $this->type === substr(strrchr(__CLASS__, '\\'), 1)
            && $this->version === Config::VERSION
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature);

        $this->logger->debug('GetTransaction validity', [$validity]);

        return $validity;
    }

    public function getResponse(): array
    {
        $block_transaction = Block::GetTransaction($this->find_thash);

        $this->logger->debug('GetTransaction response data', [$block_transaction]);

        return $block_transaction;
    }
}
