<?php

namespace src\Transaction;

use src\Status\Token;
use src\System\Decision;
use src\System\Key;
use src\System\Transaction;
use src\System\Version;

class SendToken extends Transaction
{
    public const TYPE = 'SendToken';

    protected $transaction;
    protected $thash;
    protected $public_key;
    protected $signature;

    protected $status_key;

    private $type;
    private $version;
    private $from;
    private $to;
    private $token_name;
    private $amount;
    private $transactional_data;
    private $timestamp;

    private $from_token_balance;
    private $to_token_balance;

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
        if (isset($this->transaction['to'])) {
            $this->to = $this->transaction['to'];
        }
        if (isset($this->transaction['token_name'])) {
            $this->token_name = $this->transaction['token_name'];
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
        $isValidInput = Version::isValid($this->version)
            && is_string($this->to)
            && is_numeric($this->amount)
            && is_numeric($this->timestamp)
            && $this->type === self::TYPE
            && (mb_strlen($this->to) === Key::ADDRESS_SIZE)
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature);

        if (!$isValidInput) {
            return false;
        }

        $this->retrieveBalances();

        return $this->isValidDeal($this->from_token_balance, $this->to_token_balance, $this->amount);
    }

    public function isValidDeal($fromBalance, $toBalance, $amount): bool
    {
        $fromRemain = $fromBalance - $amount;
        $toRemain = $toBalance + $amount;

        return $fromRemain >= 0
            && $fromRemain < $fromBalance
            && $toRemain > $toBalance;
    }

    public function _LoadStatus()
    {
        Token::LoadToken($this->from, $this->token_name);
        Token::LoadToken($this->to, $this->token_name);
    }

    public function _GetStatus()
    {
        $this->from_token_balance = Token::GetBalance($this->from, $this->token_name);
        $this->to_token_balance = Token::GetBalance($this->to, $this->token_name);
    }

    public function _MakeDecision()
    {
        if ((int) $this->amount > (int) $this->from_token_balance) {
            return Decision::REJECT;
        }

        return Decision::ACCEPT;
    }

    public function _SetStatus()
    {
        $this->from_token_balance = (int) $this->from_token_balance - (int) $this->amount;
        $this->to_token_balance = (int) $this->to_token_balance + (int) $this->amount;

        Token::SetBalance($this->from, $this->token_name, $this->from_token_balance);
        Token::SetBalance($this->to, $this->token_name, $this->to_token_balance);
    }

    private function retrieveBalances(): void
    {
        $this->_LoadStatus();
        Token::_Load();
        $this->_GetStatus();
    }
}
