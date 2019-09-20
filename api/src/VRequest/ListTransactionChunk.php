<?php

namespace src\VRequest;

use src\System\Block;
use src\System\Chunk;
use src\System\Key;
use src\System\Request;
use src\System\Tracker;
use src\System\Version;

class ListTransactionChunk extends Request
{
    public const TYPE = 'ListTransactionChunk';

    protected $request;
    protected $thash;
    protected $public_key;
    protected $signature;

    private $type;
    private $version;
    private $from;
    private $value;
    private $transactional_data;
    private $timestamp;

    public function initialize(array $request, string $thash, string $public_key, string $signature): void
    {
        $this->request = $request;
        $this->thash = $thash;
        $this->public_key = $public_key;
        $this->signature = $signature;

        $this->type = $this->request['type'] ?? '';
        $this->version = $this->request['version'] ?? '';
        $this->from = $this->request['from'] ?? '';
        $this->value = $this->request['value'] ?? 0;
        $this->transactional_data = $this->request['transactional_data'] ?? '';
        $this->timestamp = $this->request['timestamp'] ?? 0;
    }

    public function getValidity(): bool
    {
        return Version::isValid($this->version)
            && !empty($this->value)
            && !empty($this->timestamp)
            && $this->type === self::TYPE
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature)
            && Tracker::IsFullNode($this->from);
    }

    public function getResponse(): array
    {
        $blocks = Block::GetBlocks($this->value);
        $chunks = [];

        foreach ($blocks as $item) {
            $transaction_dir = Chunk::GetTransactionDirectory($item['block_number']);
            $chunks[] = [
                'chunk_name' => $transaction_dir . '/' . $item['blockhash'] . $item['s_timestamp'] . '.json',
                'block_info' => $item,
            ];
        }

        return ['chunks' => $chunks];
    }
}
