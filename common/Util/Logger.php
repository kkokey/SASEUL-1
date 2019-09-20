<?php

namespace src\Util;

use Monolog;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use src\System\Config;

class Logger
{
    public static function Log($obj, $option = false)
    {
        print_r('[Log]' . xdebug_call_file() . ':' . xdebug_call_line() . PHP_EOL);
        print_r($obj);
        print_r(PHP_EOL);
        if ($option) {
            exit();
        }
    }

    public static function EchoLog($obj)
    {
        print_r($obj);
        print_r(PHP_EOL);
    }

    public static function Error($obj = null)
    {
        print_r('[Error]' . xdebug_call_file() . ':' . xdebug_call_line() . PHP_EOL);

        if ($obj !== null) {
            print_r($obj);
            print_r(PHP_EOL);
        }

        exit();
    }

    public static function getLogger(string $appName): Monolog\Logger
    {
        $logger = new Monolog\Logger($appName);

        $fileHandler = new RotatingFileHandler(Config::$log_path . '/' . $appName . '.log', 30, Config::$log_level);
        $fileHandler->setFormatter(new JsonFormatter());
        $fileHandler->pushProcessor(new IntrospectionProcessor());

        $streamHandler = new StreamHandler('php://stdout', Config::$log_level);
        $streamHandler->setFormatter(new LineFormatter());
        $streamHandler->pushProcessor(new PsrLogMessageProcessor());

        $logger->pushHandler($fileHandler);
        $logger->pushHandler($streamHandler);

        return $logger;
    }

    public static function getConsoleLogger(string $appName): Monolog\Logger
    {
        $logger = new Monolog\Logger($appName);
        $handler = new StreamHandler('php://stdout');

        $output = "%datetime% > %level_name% > %message% : %context%\n";
        $handler->setFormatter(new LineFormatter($output));
        $handler->pushProcessor(new PsrLogMessageProcessor());
        $logger->pushHandler($handler);

        return $logger;
    }
}
