<?php

namespace src\Core;

use src\System\Cache;
use src\System\Chunk;
use src\System\Config;
use src\Util\RestCall;
use src\Util\TypeChecker;

class BroadcastManager
{
    protected $validators = [];
    protected $last_blockinfo = [];

    protected $structure_chunk = [
        'name' => '',
        'rows' => [],
        'count' => 0,
        'signature' => '',
        'public_key' => '',
    ];

    protected $collected_s_timestamp = 0;

    private static $instance = null;

    private $cache;
    private $rest;

    public function __construct()
    {
        $this->cache = Cache::GetInstance();
        $this->rest = RestCall::GetInstance();
    }

    public static function GetInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function Initialize($validators, $last_blockinfo)
    {
        $this->validators = $validators;
        $this->last_blockinfo = $last_blockinfo;
        $this->collected_s_timestamp = 0;
    }

    public function CollectChunks($last_s_timestamp, $s_timestamp)
    {
        foreach ($this->validators as $validator) {
            $host = $validator['host'];

            $chunks = $this->RequestChunks($host, $last_s_timestamp, $s_timestamp);

            if ($chunks === null) {
                continue;
            }

            foreach ($chunks as $chunk) {
                if (TypeChecker::StructureCheck($this->structure_chunk, $chunk) === false) {
                    continue;
                }

                Chunk::SaveBroadcastChunk($chunk);
            }
        }
    }

    public function SetCollectedStandardTimestamp($chunkname)
    {
        $s_timestamp = Chunk::GetChunkMicrotime($chunkname);

        if ((int) $this->collected_s_timestamp === 0) {
            $this->collected_s_timestamp = (int) $s_timestamp;
        } else {
            if ((int) $this->collected_s_timestamp > (int) $s_timestamp) {
                $this->collected_s_timestamp = (int) $s_timestamp;
            }
        }
    }

    public function RequestChunks($host, $last_s_timestamp, $s_timestamp)
    {
        $url = "http://{$host}/vrequest/getchunks";
        $data = [
            'min_st' => $last_s_timestamp,
            'max_st' => $s_timestamp,
        ];

        $rs = $this->rest->POST($url, $data);
        $rs = json_decode($rs, true);

        if (!isset($rs['data'])) {
            return null;
        }

        return $rs['data'];
    }

    public function GetBroadcastChunks(int $last_s_timestamp, int $next_s_timestamp, string $address = '')
    {
        $d = scandir(Config::$block->getBroadcastChunks());
        $chunks = [];

        foreach ($d as $dir) {
            $broadcast_chunk = Config::$block->getBroadcastChunks() . '/' . $dir;
            $file_timestamp = Chunk::GetChunkMicrotime($dir);

            if (!preg_match('/[0-9]+\\.json$/', $dir)) {
                continue;
            }

            if ($file_timestamp === null || !file_exists($broadcast_chunk)) {
                continue;
            }

            if ($address !== '' && !preg_match('/^' . Config::$block::CHUNK_PREFIX . $address . '_/', $dir)) {
                continue;
            }

            if ((int) $file_timestamp > (int) $next_s_timestamp || (int) $file_timestamp <= (int) $last_s_timestamp) {
                continue;
            }

            $chunks[] = $dir;
        }

        return $chunks;
    }

    public function GetMyBroadcastChunks(int $last_s_timestamp, int $next_s_timestamp)
    {
        $address = Config::$node->getAddress();

        return $this->GetBroadcastChunks($last_s_timestamp, $next_s_timestamp, $address);
    }

    public function GetChunksForCommit($last_s_timestamp, $s_timestamp)
    {
        return $this->GetBroadcastChunks($last_s_timestamp, $s_timestamp);
    }

    public function GetCollectedStandardTimestamp()
    {
        return $this->collected_s_timestamp;
    }
}
