<?php

namespace src\Core;

use src\System\Cache;
use src\System\Config;
use src\System\Key;
use src\Util\DateTime;
use src\Util\RestCall;
use src\Util\TypeChecker;

class HashManager
{
    protected $m_validators = [];
    protected $m_last_blockinfo = [];

    protected $structures = [
        'blockhash' => [
            'decision' => [
                'round_number' => 0,
                'last_blockhash' => '',
                'blockhash' => '',
                's_timestamp' => 0,
                'timestamp' => 0,
                'round_key' => '',
            ],
            'public_key' => '',
            'hash' => '',
            'signature' => '',
        ]
    ];

    protected $m_blockhashs = [];

    protected $m_best_blockhash = [];

    private static $instance = null;

    private $cache;
    private $rest;
    private $round_manager;

    public function __construct()
    {
        $this->cache = Cache::GetInstance();
        $this->rest = RestCall::GetInstance();
        $this->round_manager = RoundManager::GetInstance();
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
        $this->m_validators = $validators;
        $this->m_last_blockinfo = $last_blockinfo;

        $this->m_blockhashs = [];
        $this->m_best_blockhash = $this->structures['blockhash'];
    }

    public function SaveDecision(string $round_key, string $decision_key, $value)
    {
        $key = $round_key . $decision_key;
        $this->cache->set($key, $value);
    }

    public function ReadyBlockhash($my_block_info)
    {
        $address = Config::$node->getAddress();
        $rounds = $this->round_manager->GetRounds();

        $my_decision = $rounds[$address]['decision'];
        $round_number = $my_decision['round_number'];
        $last_blockhash = $my_decision['last_blockhash'];
        $round_key = $my_decision['round_key'];

        $now = DateTime::Microtime();

        $decision = [
            'round_number' => $round_number,
            'last_blockhash' => $last_blockhash,
            'blockhash' => $my_block_info['blockhash'],
            's_timestamp' => $my_block_info['s_timestamp'],
            'transaction_count' => $my_block_info['transaction_count'],
            'timestamp' => $now,
            'round_key' => $round_key,
        ];

        $public_key = Config::$node->getPublicKey();
        $hash = hash('sha256', json_encode($decision));
        $signature = Key::makeSignature($hash, Config::$node->getPrivateKey(), Config::$node->getPublicKey());

        $this->m_blockhashs[$address] = [
            'decision' => $decision,
            'public_key' => $public_key,
            'hash' => $hash,
            'signature' => $signature,
        ];

        $this->SaveDecision($round_key, 'blockhash', $this->m_blockhashs);
    }

    public function CollectBlockhash()
    {
        $my_address = Config::$node->getAddress();
        $rounds = $this->round_manager->GetRounds();

        $round_key = $rounds[$my_address]['decision']['round_key'];

        for ($i = 0; $i < 3; $i++) {
            $now = DateTime::Microtime();

            foreach ($this->m_validators as $validator) {
                $request = $this->RequestBlockhashs($validator['host'], $round_key);

                if (!is_array($request)) {
                    continue;
                }

                $this->SetBlockhashs($request);
            }

            $this->SaveDecision($round_key, 'blockhash', $this->m_blockhashs);

            if (count($this->m_blockhashs) === count($this->m_validators)) {
                return;
            }

            $heartbeat = DateTime::Microtime() - $now;

            if ($heartbeat < 200000) {
                usleep(200000 - $heartbeat);
            }
        }
    }

    public function RequestBlockhashs(string $host, string $round_key)
    {
        $url = "http://{$host}/vrequest/getblockhash";
        $data = [
            'key' => $round_key,
        ];

        $rs2 = $this->rest->POST($url, $data);
        $rs = json_decode($rs2, true);

        if (!isset($rs['data'])) {
            \System_Daemon::info(date('Y-m-d H:i:s') . " - {$rs2}");

            return null;
        }

        return $rs['data'];
    }

    public function SetBlockhashs($blockhashs)
    {
        foreach ($blockhashs as $address => $blockhash) {
            if (!TypeChecker::StructureCheck($this->structures['blockhash'], $blockhash)) {
                continue;
            }

            if (!$this->CheckRequest($address, $blockhash)) {
                continue;
            }

            $this->m_blockhashs[$address] = $blockhash;
        }
    }

    public function CheckRequest($address, $value): bool
    {
        $round_number = $value['decision']['round_number'];
        $last_blockhash = $value['decision']['last_blockhash'];
        $round_key = $value['decision']['round_key'];
        $public_key = $value['public_key'];
        $signature = $value['signature'];
        $hash = hash('sha256', json_encode($value['decision']));

        return Key::isValidSignature($hash, $public_key, $signature)
            && (Key::makeAddress($public_key) === $address)
            && ($this->MakeRoundKey($last_blockhash, $round_number) === $round_key);
    }

    public function MakeRoundKey($block, $round_number)
    {
        return hash('ripemd160', $block) . $round_number;
    }

    public function DecideBlockhash()
    {
        $my_address = Config::$node->getAddress();

        $my_blockhash = $this->m_blockhashs[$my_address];
        $best = $my_blockhash;
        $sign = false;

        foreach ($this->m_blockhashs as $m_blockhash) {
            $decision = $m_blockhash['decision'];
            $blockhash = $decision['blockhash'];
            $s_timestamp = $decision['s_timestamp'];

            $best_decision = $best['decision'];
            $best_blockhash = $best_decision['blockhash'];
            $best_s_timestamp = $best_decision['s_timestamp'];

            if ($blockhash !== $best_blockhash) {
                if ($s_timestamp < $best_s_timestamp) {
                    $best = $m_blockhash;

                    continue;
                }

                $sign = true;
            }
        }

        $this->m_best_blockhash = $best;

        if ($sign === true) {
            $this->m_best_blockhash = [];
        }
    }

    public function GetBestBlockhash()
    {
        if (isset($this->m_best_blockhash['decision']['blockhash'])) {
            return $this->m_best_blockhash['decision']['blockhash'];
        }

        return '';
    }
}
