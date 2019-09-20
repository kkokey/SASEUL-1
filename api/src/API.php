<?php

namespace src;

use src\System\HttpRequest;
use src\System\Terminator;

class API
{
    public $data = [];
    protected $display_params = true;
    protected $result = [];
    protected $httpRequest;

    public function Call(HttpRequest $request = null): void
    {
        if ($request == null) {
            $request = new HttpRequest($_REQUEST, $_SERVER, $_GET, $_POST);
        }
        $this->httpRequest = $request;

        $this->Exec($request);
        $this->Success();
    }

    public function Exec(HttpRequest $request = null)
    {
        if ($request == null) {
            $request = new HttpRequest($_REQUEST, $_SERVER, $_GET, $_POST);
        }
        $this->httpRequest = $request;

        $this->_init($request);
        $this->_process($request);
        $this->_end($request);
    }

    public function _init()
    {
    }

    public function _process()
    {
    }

    public function _end()
    {
    }

    public function Error403($msg = 'Forbidden')
    {
        $this->Fail(403, $msg);
    }

    public function Error404($msg = 'API Not Found')
    {
        $this->Fail(404, $msg);
    }

    public function Error($msg = 'Error', $code = 999)
    {
        $this->Fail($code, $msg);
    }

    public function getParam(array $request, string $key, array $options = [])
    {
        if (!isset($request[$key]) && !isset($options['default'])) {
            $this->Error("There is no parameter: {$key}");
        }

        $param = $request[$key] ?? $options['default'];

        if (isset($options['type']) && !static::checkType($param, $options['type'])) {
            $this->Error("Wrong parameter type: {$key}");
        }

        return $param;
    }

    public static function checkType($param, string $type): bool
    {
        if (($type === 'string') && !is_string($param)) {
            return false;
        }

        if (($type === 'numeric') && !is_numeric($param)) {
            return false;
        }

        return true;
    }

    protected function Success()
    {
        $this->result['status'] = 'success';
        $this->result['data'] = $this->data;

        if ($this->display_params === true) {
            $this->result['params'] = $_REQUEST;
        }

        $this->View();
    }

    protected function Fail($code, $msg = '')
    {
        $this->result['status'] = 'fail';
        $this->result['code'] = $code;
        $this->result['msg'] = $msg;

        $this->View();
    }

    protected function View()
    {
        try {
            header('Content-Type: application/json; charset=utf-8;');
        } catch (\Exception $e) {
            echo $e . PHP_EOL . PHP_EOL;
        }
        echo json_encode($this->result);
        Terminator::exit();
    }
}
