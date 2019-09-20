<?php

namespace src;

use src\Core\Arbiter;
use src\Core\Node;
use src\Core\Supervisor;
use src\Core\Validator;
use src\System\Config;
use src\System\Rank;
use src\System\Tracker;
use src\Util\Logger;

class Application
{
    private $m_node;
    private $m_rank;

    public function __construct()
    {
        Config::init();

        $this->m_node = new Node();
        $this->m_rank = Rank::LIGHT;

        $this->SetRank();
        $this->SetNode();
    }

    public function Main()
    {
        $this->m_node->Action();
    }

    public function SetRank()
    {
        $this->m_rank = Tracker::GetRole(Config::$node->getAddress());
    }

    public function SetNode()
    {
        switch ($this->m_rank) {
            case Rank::VALIDATOR:
                $this->m_node = new Validator();

                break;
            case Rank::SUPERVISOR:
                $this->m_node = new Supervisor();

                break;
            case Rank::ARBITER:
                $this->m_node = new Arbiter();

                break;
            default:
                Logger::Log('You are light node in saseul network ');
                exit();
        }
    }
}
