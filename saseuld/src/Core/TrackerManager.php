<?php

namespace src\Core;

use src\Method\Attributes;
use src\System\Cache;
use src\System\Config;
use src\System\Tracker;
use src\Util\RestCall;

class TrackerManager
{
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

    public function GenerateTracker()
    {
        $validators = Attributes::GetValidator();
        $supervisors = Attributes::GetSupervisor();
        $arbiters = Attributes::GetArbiter();
        $fullnodes = Attributes::GetFullNode();

        $validators_in_tracker = Tracker::GetValidatorAddress();
        $supervisors_in_tracker = Tracker::GetSupervisorAddress();
        $arbiters_in_tracker = Tracker::GetArbiterAddress();
        $fullnodes_in_tracker = Tracker::GetFullNodeAddress();

        foreach ($validators as $validator) {
            if (!in_array($validator, $validators_in_tracker)) {
                Tracker::SetValidator($validator);

                if ($validator === Config::$node->getAddress()) {
                    Tracker::SetHost($validator, Config::$node->getHost());
                }
            }
        }

        foreach ($supervisors as $supervisor) {
            if (!in_array($supervisor, $supervisors_in_tracker)) {
                Tracker::SetSupervisor($supervisor);
            }
        }

        foreach ($arbiters as $arbiter) {
            if (!in_array($arbiter, $arbiters_in_tracker)) {
                Tracker::SetArbiter($arbiter);
            }
        }

        foreach ($fullnodes_in_tracker as $fullnode) {
            if (!in_array($fullnode, $fullnodes)) {
                Tracker::SetLightNode($fullnode);
            }
        }
    }
}
