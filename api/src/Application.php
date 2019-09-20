<?php

namespace src;

use src\System\Config;
use src\System\HttpRequest;
use src\System\HttpResponse;
use src\System\HttpStatus;
use src\System\Terminator;

class Application
{
    private $handler = 'main';
    private $route = 200;

    public function __construct()
    {
        Config::init();

        $this->_DefaultSetting();
        $req = new HttpRequest($_REQUEST, $_SERVER, $_GET, $_POST);
        $resp = $this->_Route($req);
        $this->response($resp);
    }

    public function _DefaultSetting()
    {
        date_default_timezone_set('Asia/Seoul');
        session_start();
        header('Access-Control-Allow-Origin: *');

        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'json') > -1) {
            $_POST = json_decode(file_get_contents('php://input'), true);
            $_REQUEST = empty($_POST) ? $_REQUEST : array_merge($_REQUEST, $_POST);
        }
    }

    public function _Route(HttpRequest $req): HttpResponse
    {
        $uri = $req->getUri();

        if ($uri === HttpRequest::INVALID_URI) {
            return new HttpResponse(HttpStatus::NOT_FOUND);
        }

        $this->handler = ltrim(parse_url($uri)['path'], '/');

        $dir = $this->FindDir("src/API/{$this->handler}.php");

        if ($dir == false) {
            return new HttpResponse(HttpStatus::NOT_FOUND);
        }

        $class = preg_replace('/\\/{1,}/', '\\', $dir);
        $class = preg_replace('/.php$/', '', $class);

        if (!class_exists($class)) {
            return new HttpResponse(HttpStatus::NOT_FOUND);
        }

        $api = new $class();

        $api->Call($req);

        return new HttpResponse($api->getResult()['code'], $api->getResult(), $api->getHtml());
    }

    public function FindDir($full_dir)
    {
        $full_dir = "../{$full_dir}";
        $dir = preg_replace('/\\/{2,}/', '/', $full_dir);
        $dir = explode('/', $dir);

        if (count($dir) > 1) {
            $parent = $dir[0];
            $count = count($dir);

            for ($i = 1; $i < $count; $i++) {
                $find = $dir[$i];

                if ($this->FindFile($parent, $find) === false) {
                    return false;
                }

                $parent = $this->FindFile($parent, $find);

                if (strtolower($parent) == strtolower($full_dir)) {
                    return substr($parent, 2);
                }
            }
        }

        return false;
    }

    public function FindFile($parent_dir, $search_str)
    {
        if (file_exists($parent_dir)) {
            $d = scandir($parent_dir);
            $lower_d = '';

            foreach ($d as $dir) {
                if (strtolower($search_str) === strtolower($dir)) {
                    $lower_d = $dir;

                    break;
                }
            }

            return "{$parent_dir}/{$lower_d}";
        }

        return false;
    }

    private function response(HttpResponse $resp): void
    {
        http_response_code($resp->getCode());
        if (!headers_sent()) {
            $header = $resp->getHeader();

            foreach ($header as $key => $value) {
                header("${key}: ${value}");
            }
        }

        if ($resp->isHtmlBody()) {
            echo $resp->getHtmlBody();
        } else {
            echo json_encode($resp->getData());
        }

        Terminator::exit();
    }
}
