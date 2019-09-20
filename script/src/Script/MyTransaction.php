<?php

namespace src\Script;

use src\Script;
use src\System\Config;
use src\System\Database;
use src\Util\Logger;
use src\Util\Parser;

class MyTransaction extends Script
{
    public function _process()
    {
        echo PHP_EOL;

        $transactions = $this->GetTransaction();

        foreach ($transactions as $transaction) {
            Logger::EchoLog($transaction);
        }
    }

    public function GetTransaction()
    {
        $db = Database::GetInstance();
        $public_key = Config::$node->getPublicKey();

        $namespace = 'saseul_committed.transactions';
        $filter = ['public_key' => $public_key];
        $opt = ['sort' => ['timestamp' => -1]];
        $rs = $db->Query($namespace, $filter, $opt);

        $max = 5;
        $count = 0;
        $transactions = [];

        foreach ($rs as $item) {
            $item = Parser::obj2array($item);
            unset($item['_id']);

            $transactions[] = $item;
            $count = $count + 1;

            if ($count >= $max) {
                break;
            }
        }

        return $transactions;
    }
}
