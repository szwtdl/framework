<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */
namespace Szwtdl\Framework;

use Szwtdl\Framework\Server\Http;
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
                break;
            case 'mqtt':
                self::echoError('待开放中');
                return;
                break;
            default:
                self::echoError('暂未开放自定义服务');
        }
        switch ($command[1]) {
            case 'start':
                if ($server->checkEnv()) {
                    return;
                }
                self::echoSuccess('=============Swoole framework ' . swoole_version() . '==================');
                self::echoSuccess('=============Szwtdl framework ' . self::VERSION . '==================');
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
            default:
                self::echoError("use {$argv[0]} [http:start, ws:start, mqtt:start, main:start]");
        }
    }
}
