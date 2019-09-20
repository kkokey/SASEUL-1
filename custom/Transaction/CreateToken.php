<?php

namespace src\Transaction;

use src\Status\Attributes;
use src\Status\Token;
use src\Status\TokenList;
use src\System\Config;
use src\System\Decision;
use src\System\Key;
use src\System\Role;
use src\System\Transaction;
use src\System\Version;

class CreateToken extends Transaction
{
    public const TYPE = 'CreateToken';

    protected $transaction;
    protected $thash;
    protected $public_key;
    protected $signature;

    protected $status_key;

    private $type;
    private $version;
    private $from;
    private $amount;
    private $token_name;
    private $token_publisher;
    private $transactional_data;
    private $timestamp;

    private $from_role;
    private $publish_token_info;
    private $from_token_balance;

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
        if (isset($this->transaction['token_name'])) {
            $this->token_name = $this->transaction['token_name'];
        }
        if (isset($this->transaction['token_publisher'])) {
            $this->token_publisher = $this->transaction['token_publisher'];
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
            && is_string($this->token_name)
            && is_string($this->token_publisher)
            && is_numeric($this->amount)
            && is_numeric($this->timestamp)
            && $this->type === self::TYPE
            && (mb_strlen($this->token_name) < 64)
            && (mb_strlen($this->token_publisher) === Key::ADDRESS_SIZE)
            && ($this->amount <= Config::$genesis->getCoinValue())
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature);
    }

    public function _LoadStatus()
    {
        Token::LoadToken($this->from, $this->token_name);
        TokenList::LoadTokenList($this->token_name);
        Attributes::LoadRole($this->from);
    }

    public function _GetStatus()
    {
        $this->from_token_balance = Token::GetBalance($this->from, $this->token_name);
        $this->publish_token_info = TokenList::GetInfo($this->token_name);
        $this->from_role = Attributes::GetRole($this->from);
    }

    public function _MakeDecision()
    {
        if ($this->publish_token_info == []) {
            if ($this->from_role === Role::VALIDATOR) {
                return Decision::ACCEPT;
            }
        } else {
            if (isset($this->publish_token_info['publisher']) && $this->publish_token_info['publisher'] === $this->from) {
                return Decision::ACCEPT;
            }
        }

        return Decision::REJECT;
    }

    public function _SetStatus()
    {
        $total_amount = 0;

        if (isset($this->publish_token_info['total_amount'])) {
            $total_amount = $this->publish_token_info['total_amount'];
        }

        $total_amount = $total_amount + (int) $this->amount;
        $this->from_token_balance = (int) $this->from_token_balance + (int) $this->amount;
        $this->publish_token_info = [
            'publisher' => $this->from,
            'total_amount' => $total_amount,
        ];

        Token::SetBalance($this->from, $this->token_name, $this->from_token_balance);
        TokenList::SetInfo($this->token_name, $this->publish_token_info);
    }
}
