<?php

namespace src\System;

use src\Util\Parser;

class Block
{
    public static function GetCount()
    {
        $db = Database::GetInstance();

        $command = [
            'count' => 'blocks',
            'query' => [],
        ];

        $rs = $db->Command('saseul_committed', $command);
        $count = 0;

        foreach ($rs as $item) {
            $count = $item->n;

            break;
        }

        return $count;
    }

    public static function GetLastBlock()
    {
        $db = Database::GetInstance();

        $ret = [
            'block_number' => 0,
            'last_blockhash' => '',
            'blockhash' => '',
            'transaction_count' => 0,
            's_timestamp' => 0,
            'timestamp' => 0,
        ];

        $namespace = 'saseul_committed.blocks';
        $query = [];
        $opt = ['sort' => ['timestamp' => -1]];
        $rs = $db->Query($namespace, $query, $opt);

        foreach ($rs as $item) {
            $item = Parser::obj2array($item);

            if (isset($item['block_number'])) {
                $ret['block_number'] = $item['block_number'];
            }
            if (isset($item['last_blockhash'])) {
                $ret['last_blockhash'] = $item['last_blockhash'];
            }
            if (isset($item['blockhash'])) {
                $ret['blockhash'] = $item['blockhash'];
            }
            if (isset($item['transaction_count'])) {
                $ret['transaction_count'] = $item['transaction_count'];
            }
            if (isset($item['s_timestamp'])) {
                $ret['s_timestamp'] = $item['s_timestamp'];
            }
            if (isset($item['timestamp'])) {
                $ret['timestamp'] = $item['timestamp'];
            }

            break;
        }

        return $ret;
    }

    public static function GetBlock($blockhash)
    {
        $namespace = 'saseul_committed.blocks';
        $query = ['blockhash' => $blockhash];

        return self::GetDatas($namespace, 1, $query)[0];
    }

    public static function GetBlocks($block_number = 1, $max_count = 100)
    {
        $namespace = 'saseul_committed.blocks';
        $query = ['block_number' => ['$gte' => $block_number]];
        $opt = [
            'sort' => ['timestamp' => 1],
            'limit' => $max_count,
        ];

        return self::GetDatas($namespace, $query, $opt);
    }

    public static function GetLastBlocks($max_count = 100, $block_number = 0)
    {
        $namespace = 'saseul_committed.blocks';
        $query = [];
        $opt = [
            'sort' => ['timestamp' => -1],
            'limit' => $max_count,
        ];

        if ($block_number > 0) {
            $query = ['block_number' => ['$lte' => $block_number]];
        }

        return self::GetDatas($namespace, $query, $opt);
    }

    public static function GetLastTransactions($max_count = 100, $address = '')
    {
        $namespace = 'saseul_committed.transactions';
        $query = [];
        $opt = [
            'sort' => ['timestamp' => -1],
            'limit' => $max_count,
        ];

        if ($address !== '') {
            $query = [
                '$or' => [
                    ['transaction.from' => $address],
                    ['transaction.to' => $address]
                ]
            ];
        }

        return self::GetDatas($namespace, $query, $opt);
    }

    public static function GetTransaction(string $thash): array
    {
        $namespace = 'saseul_committed.transactions';
        $query = ['thash' => $thash];
        $opt = [];

        $data = self::GetDatas($namespace, $query, $opt);

        return (count($data) > 0) ? $data[0] : [];
    }

    public static function GetTransactionsInBlock($blockhash, $max_count = 100)
    {
        $namespace = 'saseul_committed.transactions';
        $query = ['block' => $blockhash];
        $opt = [
            'sort' => ['timestamp' => -1],
            'limit' => $max_count,
        ];

        return self::GetDatas($namespace, $query, $opt);
    }

    public static function GetTransactions(
        int $limit = 100,
        int $skip = 0,
        string $publicKey = '',
        string $address = '',
        string $from = '',
        string $to = ''
    ) {
        $namespace = 'saseul_committed.transactions';
        $andConditions = [];
        if (!empty($publicKey)) {
            $andConditions[] = ['public_key' => $publicKey];
        }
        if (!empty($address)) {
            $andConditions[] = [
                '$or' => [
                    ['transaction.from' => $address],
                    ['transaction.to' => $address]
                ]
            ];
        }
        if (!empty($from)) {
            $andConditions[] = ['transaction.from' => $from];
        }
        if (!empty($to)) {
            $andConditions[] = ['transaction.to' => $to];
        }
        $query = ['$and' => $andConditions];

        $opt = [
            'sort' => ['timestamp' => -1],
            'limit' => $limit,
        ];

        if ($skip > 0) {
            $opt['skip'] = $skip;
        }

        return self::GetDatas($namespace, $query, $opt);
    }

    public static function GetDatas($namespace, $query = [], $opt = [])
    {
        $db = Database::GetInstance();
        $rs = $db->Query($namespace, $query, $opt);
        $datas = [];

        foreach ($rs as $item) {
            $data = Parser::obj2array($item);
            unset($data['_id']);
            $datas[] = $data;
        }

        return $datas;
    }
}
