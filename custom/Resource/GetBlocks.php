<?php

namespace src\Resource;

use src\System\Block;
use src\System\Resource;
use src\System\Version;

class GetBlocks extends Resource
{
    public const TYPE = 'GetBlocks';
    private const MAX_COUNT = 100;
    private const DEFAULT_COUNT = 20;
    private const DEFAULT_BLOCK_NUMBER = 0;

    protected $request;
    protected $thash;
    protected $public_key;
    protected $signature;

    private $type;
    private $version;
    private $from;
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
        $this->transactional_data = $this->request['transactional_data'] ?? '';
        $this->timestamp = $this->request['timestamp'] ?? 0;
    }

    public function getValidity(): bool
    {
        return Version::isValid($this->version)
            && $this->type === self::TYPE;
    }

    public function getResponse(): array
    {
        $count = $this->getCount();
        $blockNumber = $this->getBlockNumber();

        return [
            'blocks' => Block::GetLastBlocks($count, $blockNumber)
        ];
    }

    private function getCount(): int
    {
        $requestCount = self::DEFAULT_COUNT;
        if (isset($this->request['count'])) {
            $requestCount = (int) $this->request['count'];
        }

        return min($requestCount, self::MAX_COUNT);
    }

    private function getBlockNumber(): int
    {
        $blockNumber = self::DEFAULT_BLOCK_NUMBER;
        if (isset($this->request['number'])) {
            $blockNumber = (int) $this->request['number'];
        }

        return $blockNumber;
    }
}
