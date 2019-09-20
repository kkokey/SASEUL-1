<?php

namespace src\Core;

class Node
{
    private $sync_fail_count = 0;

    public function Action()
    {
    }

    public function resetFailCount()
    {
        $this->sync_fail_count = 0;
    }

    public function increaseFailCount()
    {
        $this->sync_fail_count++;
    }

    public function isTimeToSeparation()
    {
        return $this->sync_fail_count >= 10;
    }

    public function getFailCount()
    {
        return $this->sync_fail_count;
    }
}
