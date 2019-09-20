<?php

namespace src\Config;

use src\Util\Config;

class Genesis
{
    private $host;
    private $address;
    private $coinValue;
    private $depositValue;

    public function __construct()
    {
        $this->host = Config::getFromEnv('GENESIS_HOST');
        $this->address = Config::getFromEnv('GENESIS_ADDRESS');
        $this->coinValue = Config::getFromEnv('GENESIS_COIN_VALUE');
        $this->depositValue = Config::getFromEnv('GENESIS_DEPOSIT_VALUE');
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getCoinValue()
    {
        return $this->coinValue;
    }

    public function getDepositValue()
    {
        return $this->depositValue;
    }

    public function getKey(): array
    {
        return [
            'genesis_message' => 'Imagine Beyond and Invent Whatever, Wherever - Published by ArtiFriends. '
                . 'Thank you for help - YJ.Lee, JW.Lee, SH.Shin, YS.Han, WJ.Choi, DH.Kang, HG.Lee, KH.Kim, '
                . 'HK.Lee, JS.Han, SM.Park, SJ.Chae, YJ.Jeon, KM.Lee, JH.Kim, '
                . 'mika, ashal, datalater, namedboy, masterguru9, ujuc, johngrib, kimpi, greenmon, '
                . 'HS.Lee, TW.Nam, EH.Park, MJ.Mok',
            'special_thanks' => 'Michelle, Francis, JS.Han, Pang, Jeremy, JG, TY.Lee, SH.Ji, HK.Lim, IS.Choi, '
                . 'CH.Park, SJ.Park, DH.Shin and CK.Park',
            'etc_messages' => [
                [
                    'writer' => 'Michelle.Kim',
                    'message' => 'I love jjal. ',
                ],
                [
                    'writer' => 'Francis.W.Han',
                    'message' => 'khan@artifriends.com, I\'m here with JG and SK. ',
                ],
                [
                    'writer' => 'JG.Lee',
                    'message' => 'In the beginning God created the blocks and the chains. '
                        . 'God said, \'Let there be SASEUL\' and saw that it was very good. ',
                ],
                [
                    'writer' => 'namedboy',
                    'message' => 'This is \'SASEUL\', Welcome to new world.',
                ],
                [
                    'writer' => 'ujuc',
                    'message' => 'Hello Saseul! :)',
                ]
            ]
        ];
    }
}
