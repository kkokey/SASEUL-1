<?php

namespace src\Core;

use src\System\Block;
use src\System\Tracker;
use src\Util\Logger;

class Arbiter extends Node
{
    private $validators;
    private $last_blockinfo;

    private $round_manager;
    private $commit_manager;
    private $sync_manager;

    private $my_round_number;
    private $net_round_number;
    private $net_round_leader;
    private $net_s_timestamp;

    private $sync_info;
    private $expect_blockinfo;
    private $expect_blockhash;

    public function __construct()
    {
        $this->round_manager = RoundManager::GetInstance();
        $this->commit_manager = CommitManager::GetInstance();
        $this->sync_manager = SyncManager::GetInstance();
    }

    public function Initialize()
    {
        $this->validators = Tracker::GetValidator();
        $this->last_blockinfo = Block::GetLastBlock();

        $this->round_manager->Initialize($this->validators, $this->last_blockinfo);
        $this->commit_manager->Initialize();
        $this->sync_manager->Initialize($this->validators, $this->last_blockinfo);
    }

    public function ProcessRound()
    {
        $this->round_manager->ReadyRound();
        $this->round_manager->CollectRound();

        $this->my_round_number = $this->round_manager->GetMyRoundNumber();
        $this->net_round_number = $this->round_manager->GetNetRoundNumber();
        $this->net_round_leader = $this->round_manager->GetNetRoundLeader();
        $this->net_s_timestamp = $this->round_manager->GetNetStandardTimestamp();
    }

    public function Sync()
    {
        $this->sync_manager->ReadySync();
        $this->sync_manager->SetSyncInfo();

        $this->sync_info = $this->sync_manager->GetSyncInfo();

        $sync_min_timestamp = $this->sync_info['min_timestamp'];
        $sync_max_timestamp = $this->sync_info['max_timestamp'];
        $sync_transactions_chunks = $this->sync_info['transactions_chunks'];

        $this->commit_manager->SetBlockInfo($this->my_round_number, $this->last_blockinfo, $sync_max_timestamp);
        $this->commit_manager->Precommit($sync_transactions_chunks, $sync_min_timestamp, $sync_max_timestamp);

        $this->commit_manager->MakeDecision();
        $this->commit_manager->SetExpectBlockhash();

        $this->expect_blockinfo = $this->commit_manager->GetExpectBlockInfo();
        $this->expect_blockhash = $this->expect_blockinfo['blockhash'];
    }

    public function Action()
    {
        $this->Initialize();
        $this->ProcessRound();

        if ($this->my_round_number === $this->net_round_number) {
            return;
        }

        $this->Sync();

        if ($this->sync_info['blockhash'] === $this->expect_blockhash) {
            $this->commit();
            $this->success();

            return;
        }

        if ($this->isTimeToSeparation()) {
            $this->banish();
            $this->success();

            return;
        }

        $this->fail();
    }

    private function banish(): void
    {
        Tracker::Banish($this->net_round_leader);

        Logger::Log('[Banish round leader] ');
        Logger::Log('my round number : ' . $this->my_round_number);
        Logger::Log('net round number : ' . $this->net_round_number);
        Logger::Log('net round leader : ' . $this->net_round_leader);
        Logger::Log('net standard timestamp : ' . $this->net_s_timestamp);
        Logger::Log('sync info : ' . json_encode($this->sync_info));
        Logger::Log('expect info : ' . json_encode($this->expect_blockinfo));
        Logger::Log('sync blockhash : ' . $this->sync_info['blockhash']);
        Logger::Log('expect blockhash : ' . $this->expect_blockhash);
    }

    private function commit(): void
    {
        $this->sync_manager->SyncTransactionChunk();
        $this->commit_manager->Commit();
        $this->commit_manager->End();
    }

    private function success(): void
    {
        $this->resetFailCount();
    }

    private function fail(): void
    {
        $this->increaseFailCount();
        Logger::Log('[Sync fail] ');
        Logger::Log('sync blockhash : ' . $this->sync_info['blockhash']);
        Logger::Log('expect blockhash : ' . $this->expect_blockhash);
    }
}
