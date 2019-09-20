<?php

namespace src;

class Manager
{
    private $application;

    public function __construct()
    {
        $this->_DefaultSetting();
        $this->application = new Application();
    }

    public function _DefaultSetting()
    {
        date_default_timezone_set('Asia/Seoul');
        ini_set('memory_limit', '4G');
    }

    public function Main()
    {
        $this->application->Main();
    }
}
