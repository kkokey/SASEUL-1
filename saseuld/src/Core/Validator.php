<?php

namespace src\Core;

use src\System\Block;
use src\System\Tracker;
use src\Util\Logger;

class Validator extends Node
{
    private $validators;
    private $last_blockinfo;

    private $my_round_number;
    private $net_round_number;
    private $net_round_leader;
    private $net_s_timestamp;

    private $sync_info;
    private $expect_transaction_chunks;
    private $expect_blockinfo;
    private $expect_blockhash;

    private $round_manager;
    private $broadcast_manager;
    private $commit_manager;
    private $hash_manager;
    private $sync_manager;
    private $tracker_manager;

    public function __construct()
    {
        $this->round_manager = RoundManager::GetInstance();
        $this->broadcast_manager = BroadcastManager::GetInstance();
        $this->commit_manager = CommitManager::GetInstance();
        $this->hash_manager = HashManager::GetInstance();
        $this->sync_manager = SyncManager::GetInstance();
        $this->tracker_manager = TrackerManager::GetInstance();
    }

    public function Initialize()
    {
        $this->validators = Tracker::GetAdmittedValidator();
        $this->last_blockinfo = Block::GetLastBlock();

        $this->round_manager->Initialize($this->validators, $this->last_blockinfo);
        $this->broadcast_manager->Initialize($this->validators, $this->last_blockinfo);
        $this->hash_manager->Initialize($this->validators, $this->last_blockinfo);
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

    public function Consensus()
    {
        $this->broadcast_manager->CollectChunks($this->last_blockinfo['s_timestamp'], $this->net_s_timestamp);

        $this->expect_transaction_chunks = $this->broadcast_manager->GetChunksForCommit($this->last_blockinfo['s_timestamp'], $this->net_s_timestamp);

        $this->commit_manager->SetBlockInfo($this->net_round_number, $this->last_blockinfo, $this->net_s_timestamp);
        foreach ($this->expect_transaction_chunks as $chunkname) {
            $this->commit_manager->PrecommitBroadcastChunk($chunkname);
        }

        $this->commit_manager->MakeDecision();
        $this->commit_manager->SetExpectBlockhash();
        $this->expect_blockinfo = $this->commit_manager->GetExpectBlockInfo();

        $this->hash_manager->ReadyBlockhash($this->expect_blockinfo);
        $this->hash_manager->CollectBlockhash();
        $this->hash_manager->DecideBlockhash();

        $this->expect_blockhash = $this->hash_manager->GetBestBlockhash();
    }

    public function Action()
    {
        $this->Initialize();
        $this->ProcessRound();

        if ($this->my_round_number !== $this->net_round_number) {
            $this->Sync();

            if ($this->sync_info['blockhash'] === $this->expect_blockhash) {
                $this->sync_manager->SyncTransactionChunk();
                $this->commit_manager->Commit();
                $this->commit_manager->End();
                $this->resetFailCount();

                return;
            }

            if ($this->isTimeToSeparation()) {
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

                $this->resetFailCount();

                return;
            }

            $this->increaseFailCount();
            Logger::Log('[Sync fail] ');
            Logger::Log('sync blockhash : ' . $this->sync_info['blockhash']);
            Logger::Log('expect blockhash : ' . $this->expect_blockhash);

            return;
        }

        if ($this->net_s_timestamp === 0) {
            usleep(500000);

            return;
        }

        $this->Consensus();

        if ($this->expect_blockinfo['blockhash'] === $this->expect_blockhash) {
            $this->commit_manager->Commit();
            $this->commit_manager->MakeTransactionChunk($this->expect_transaction_chunks);
            $this->commit_manager->End();
            $this->tracker_manager->GenerateTracker();

            return;
        }
    }
}
