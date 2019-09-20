<?php

namespace src\Core;

use src\System\Cache;
use src\System\Config;

class CacheManager
{
    protected $m_rounds;
    protected $m_my_round_number;
    protected $m_net_round_number;
    protected $m_round_keys = [];
    private $cache;

    public function __construct()
    {
        $this->cache = Cache::GetInstance();
    }

    public function LoadRound()
    {
        $m_rounds = $this->cache->get('rounds');

        if ($m_rounds != false) {
            $this->m_rounds = $m_rounds;
        } else {
            return;
        }

        $this->SetNetRound();
    }

    public function SetNetRound()
    {
        $my_address = Config::$node->getAddress();

        $this->m_my_round_number = $this->m_rounds[$my_address]['decision']['round_number'];
        $this->m_round_keys[] = $this->m_rounds[$my_address]['decision']['round_key'];
        $this->m_net_round_number = $this->m_my_round_number;

        foreach ($this->m_rounds as $round) {
            $decision = $round['decision'];
            $round_number = $decision['round_number'];

            if ($round_number > $this->m_net_round_number) {
                $this->m_net_round_number = $round_number;
                $this->m_round_keys[] = $decision['round_key'];
            }
        }
    }

    public function GetMyRoundNumber()
    {
        return $this->m_my_round_number;
    }

    public function GetNetRoundNumber()
    {
        return $this->m_net_round_number;
    }

    public function GetRoundKeys()
    {
        return $this->m_round_keys;
    }
}
