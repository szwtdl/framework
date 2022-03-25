<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://wiki.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */

namespace Szwtdl\Framework;

use Szwtdl\Framework\Contract\RequestInterface;
use Szwtdl\Framework\Server\Http;
use Szwtdl\Framework\Server\Mqtt;
use Szwtdl\Framework\Server\WebSocket;

class Application
{
    public const VERSION = '0.0.1';

    public static $route;

    public static function version()
    {
        return self::VERSION;
    }

    public static function println($strings)
    {
        echo $strings . PHP_EOL;
    }

    public static function echoSuccess($msg)
    {
        self::println('[' . date('Y-m-d H:i:s') . '] [INFO] ' . "\033[32m{$msg}\033[0m");
    }

    public static function echoError($msg)
    {
        self::println('[' . date('Y-m-d H:i:s') . '] [ERROR] ' . "\033[31m{$msg}\033[0m");
    }

    public static function run()
    {
        global $argv;
        $count = count($argv);
        $funcName = $argv[$count - 1];
        $command = explode(':', $funcName);
        $className = new \stdClass();
        switch ($command[0]) {
            case 'http':
                $className = Http::class;
                $server = new $className();
                break;
            case 'ws':
                $className = WebSocket::class;
                $server = new $className();
                break;
            case 'mqtt':
                $className = Mqtt::class;
                $server = new $className();
                break;
            default:
                self::echoError('暂未开放自定义服务');
        }
        switch ($command[1]) {
            case 'start':
                if ($server->checkEnv()) {
                    return;
                }
                $server->start();
                break;
            case 'stop':
                if ($server->checkEnv()) {
                    $server->stop();
                }
                break;
            case 'reload':
                if ($server->checkEnv()) {
                    $server->reload();
                }
                break;
            case 'watch':
                $server->watch();
                break;
            default:
                self::echoError("use {$argv[0]} [http:start, ws:start, mqtt:start, main:start]");
        }
    }
}
