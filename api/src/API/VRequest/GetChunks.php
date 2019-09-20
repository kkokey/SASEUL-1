<?php

namespace src\API\VRequest;

use src\API;
use src\System\Cache;
use src\System\Chunk;
use src\System\Config;
use src\Util\DateTime;

class GetChunks extends API
{
    private $cache;

    private $min_st;
    private $max_st;
    private $api_chunks = [];
    private $broadcast_chunks = [];

    private $chunks = [];

    public function __construct()
    {
        $this->cache = Cache::GetInstance();
    }

    public function _init()
    {
        $this->min_st = (int) $this->getParam($_REQUEST, 'min_st', ['default' => 0]);
        $this->max_st = (int) $this->getParam($_REQUEST, 'max_st', ['default' => DateTime::Microtime()]);

        if ($this->max_st === 0) {
            $this->max_st = DateTime::Microtime();
        }
    }

    public function _process()
    {
        $this->api_chunks = [];

        $this->SetAPIChunks($this->min_st, $this->max_st);
        $this->SetBroadcastChunks($this->min_st, $this->max_st);

        foreach ($this->api_chunks as $chunkname) {
            $this->ProcessAPIChunk($chunkname);
        }

        foreach ($this->broadcast_chunks as $chunkname) {
            $this->ProcessBroadcastChunks($chunkname);
        }

        $this->data = $this->chunks;
    }

    public function SetAPIChunks($min_s_timestamp, $max_s_timestamp)
    {
        $d = scandir(Config::$block->getApiChunks());

        foreach ($d as $dir) {
            $file_timestamp = Chunk::GetChunkMicrotime($dir);

            if (!preg_match('/[0-9]+\\.json$/', $dir)) {
                continue;
            }

            if ($file_timestamp === null) {
                continue;
            }

            if ((int) $file_timestamp > (int) $min_s_timestamp && (int) $file_timestamp <= (int) $max_s_timestamp) {
                $this->api_chunks[] = $dir;
            }
        }
    }

    public function ProcessAPIChunk($chunkname)
    {
        $address = Config::$node->getAddress();
        $filename = Config::$block->getApiChunks() . '/' . $chunkname;

        $name = str_replace('_', "_{$address}_", $chunkname);
        $rows = Chunk::GetChunk($filename);
        $count = count($rows);
        $signature = $this->GetContentSignature($chunkname, $rows);
        $public_key = Config::$node->getPublicKey();

        $this->chunks[] = [
            'name' => $name,
            'rows' => $rows,
            'count' => $count,
            'signature' => $signature,
            'public_key' => $public_key,
        ];
    }

    public function GetContentSignature($chunkname, $contents)
    {
        $key = 'content_sig_' . $chunkname;
        $sig = $this->cache->get($key);

        if ($sig == false) {
            $sig = Chunk::GetContentsSignature($contents);
            $this->cache->set($key, $sig);
        }

        return $sig;
    }

    public function SetBroadcastChunks($min_s_timestamp, $max_s_timestamp)
    {
        $d = scandir(Config::$block->getBroadcastChunks());

        foreach ($d as $dir) {
            $file_timestamp = Chunk::GetChunkMicrotime($dir);

            if (!preg_match('/[0-9]+\\.json$/', $dir)) {
                continue;
            }

            if ($file_timestamp === null) {
                continue;
            }

            if ((int) $file_timestamp > (int) $min_s_timestamp && (int) $file_timestamp <= (int) $max_s_timestamp) {
                $this->broadcast_chunks[] = $dir;
            }
        }
    }

    public function ProcessBroadcastChunks($chunkname)
    {
        $filename = Config::$block->getBroadcastChunks() . '/' . $chunkname;
        $keyname = preg_replace('/\.json$/', '.key', $filename);

        if (!file_exists($filename) || !file_exists($keyname)) {
            return;
        }

        $keys = Chunk::GetKey($keyname);

        if ($keys === null) {
            return;
        }

        $name = $chunkname;
        $rows = Chunk::GetChunk($filename);
        $count = count($rows);
        $signature = $keys['signature'];
        $public_key = $keys['public_key'];

        $this->chunks[] = [
            'name' => $name,
            'rows' => $rows,
            'count' => $count,
            'signature' => $signature,
            'public_key' => $public_key,
        ];
    }
}
