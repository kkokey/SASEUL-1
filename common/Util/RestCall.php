<?php

namespace src\Util;

class RestCall
{
    protected static $instance = null;

    protected $rest;
    protected $timeout;
    protected $info;

    public function __construct($timeout = 15)
    {
        $this->timeout = $timeout;
        $this->info = null;
    }

    public static function GetInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function GET($url, $ssl = false, $header = [])
    {
        $this->rest = curl_init();

        curl_setopt($this->rest, CURLOPT_URL, $url);
        curl_setopt($this->rest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->rest, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($this->rest, CURLOPT_TIMEOUT, $this->timeout);

        if (count($header) > 0) {
            curl_setopt($this->rest, CURLOPT_HTTPHEADER, $header);
        }

        $returnVal = curl_exec($this->rest);
        $this->info = curl_getinfo($this->rest);
        curl_close($this->rest);

        return $returnVal;
    }

    public function POST($url, $data = [], $ssl = false, $header = [])
    {
        $this->rest = curl_init();

        curl_setopt($this->rest, CURLOPT_URL, $url);
        curl_setopt($this->rest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->rest, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($this->rest, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->rest, CURLOPT_POST, true);
        curl_setopt($this->rest, CURLOPT_POSTFIELDS, $data);

        if (is_array($data)) {
            curl_setopt($this->rest, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($this->rest, CURLOPT_POSTFIELDS, $data);
        }

        if (count($header) > 0) {
            curl_setopt($this->rest, CURLOPT_HTTPHEADER, $header);
        }

        $returnVal = curl_exec($this->rest);
        $this->info = curl_getinfo($this->rest);
        curl_close($this->rest);

        return $returnVal;
    }

    public function WITHCURL($curl_string)
    {
        return shell_exec($curl_string);
    }

    public function INFO()
    {
        return $this->info;
    }

    public function DataToString($datas)
    {
        $returnStr = '';

        if (gettype($datas) == 'array' && count($datas) > 0) {
            $conStr = '';

            foreach ($datas as $key => $value) {
                $returnStr .= $conStr . $key . '=' . $value;
                $conStr = '&';
            }
        }

        return $returnStr;
    }
}
