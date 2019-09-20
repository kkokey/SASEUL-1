<?php

namespace src\Resource;

use src\System\Block;
use src\System\Chunk;
use src\System\Config;
use src\System\Key;
use src\System\Resource;
use src\Util\Host;
use src\Util\Logger;

class Genesis extends Resource
{
    private $logger;

    private $request;
    private $thash;
    private $public_key;
    private $signature;

    private $type;
    private $version;
    private $from;
    private $amount;
    private $transactional_data;
    private $timestamp;

    public function __construct()
    {
        $this->logger = Logger::getLogger('custom');
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
        $this->amount = $this->request['amount'] ?? '';
        $this->transactional_data = $this->request['transactional_data'] ?? '';
        $this->timestamp = $this->request['timestamp'] ?? '';
    }

    public function process(): void
    {
        $genesis_transaction_contents = [
            'transaction' => $this->request,
            'public_key' => $this->public_key,
            'signature' => $this->signature
        ];

        Chunk::SaveAPIChunk($genesis_transaction_contents, $this->timestamp);
    }

    public function getValidity(): bool
    {
        $validity = !empty($this->timestamp)
            && $this->type === substr(strrchr(__CLASS__, '\\'), 1)
            && $this->version === Config::VERSION
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature)
            && Host::isValidAddress($this->from)
            && $this->checkEmptyBlock()
        ;

        $this->logger->debug('validity', [$validity]);

        return $validity;
    }

    public function getResponse(): array
    {
        return ['status' => 'success'];
    }

    public function checkEmptyBlock(): bool
    {
        if ($this->from !== Config::$genesis->getAddress() || (int) Block::GetCount() > 0) {
            return false;
        }

        return true;
    }
}
