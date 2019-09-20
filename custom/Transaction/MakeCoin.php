<?php

namespace src\Transaction;

use src\Status\Attributes;
use src\Status\Coin;
use src\System\Config;
use src\System\Decision;
use src\System\Key;
use src\System\Role;
use src\System\Transaction;
use src\System\Version;

class MakeCoin extends Transaction
{
    public const TYPE = 'MakeCoin';

    protected $transaction;
    protected $thash;
    protected $public_key;
    protected $signature;

    private $type;
    private $version;
    private $from;
    private $amount;
    private $transactional_data;
    private $timestamp;

    private $from_balance;
    private $from_role;

    public function _Init($transaction, $thash, $public_key, $signature)
    {
        $this->transaction = $transaction;
        $this->thash = $thash;
        $this->public_key = $public_key;
        $this->signature = $signature;

        if (isset($this->transaction['type'])) {
            $this->type = $this->transaction['type'];
        }
        if (isset($this->transaction['version'])) {
            $this->version = $this->transaction['version'];
        }
        if (isset($this->transaction['from'])) {
            $this->from = $this->transaction['from'];
        }
        if (isset($this->transaction['amount'])) {
            $this->amount = $this->transaction['amount'];
        }
        if (isset($this->transaction['transactional_data'])) {
            $this->transactional_data = $this->transaction['transactional_data'];
        }
        if (isset($this->transaction['timestamp'])) {
            $this->timestamp = $this->transaction['timestamp'];
        }
    }

    public function _GetValidity(): bool
    {
        return Version::isValid($this->version)
            && is_numeric($this->amount)
            && is_numeric($this->timestamp)
            && $this->type === self::TYPE
            && $this->isValidAmount($this->amount)
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature);
    }

    public function isValidAmount($amount): bool
    {
        return $amount > 0
            && ($amount <= Config::$genesis->getCoinValue());
    }

    public function _LoadStatus()
    {
        Attributes::LoadRole($this->from);
        Coin::LoadBalance($this->from);
    }

    public function _GetStatus()
    {
        $this->from_role = Attributes::GetRole($this->from);
        $this->from_balance = Coin::GetBalance($this->from);
    }

    public function _MakeDecision()
    {
        if ($this->from_role !== Role::VALIDATOR) {
            return Decision::REJECT;
        }

        return Decision::ACCEPT;
    }

    public function _SetStatus()
    {
        $this->from_balance = $this->from_balance + $this->amount;
        Coin::SetBalance($this->from, $this->from_balance);
    }
}
