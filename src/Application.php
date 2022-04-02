<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://wiki.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */

namespace Szwtdl\Framework;

use Szwtdl\Framework\Server\Http;
use Szwtdl\Framework\Server\Mqtt;
use Szwtdl\Framework\Server\WebSocket;

class Application
{
    public const VERSION = '1.0.0';

    public static $route;

    public static function version()
    {
        return self::VERSION;
    }

    public static function println($strings)
    {
        echo $strings . PHP_EOL;
    }

    public static function welcome($host)
    {
        echo "\033[32m\t                   _      _ _ 
\t ___ ______      _| |_ __| | |
\t/ __|_  /\ \ /\ / / __/ _` | |
\t\__ \/ /  \ V  V /| || (_| | |
\t|___/___|  \_/\_/  \__\__,_|_|\033[0m\n";
        self::println("\033[32m ============================================\033[0m");
        self::println("\033[32m‖ \tSwoole " . swoole_version() . "\t\t\t    ‖\033[0m");
        self::println("\033[32m‖ \tFramework " . self::VERSION . "\t\t\t    ‖\033[0m");
        self::println("\033[32m‖ \tListen {$host}\t    ‖\033[0m");
        self::println("\033[32m‖ \tSite https://docs.szwtdl.cn\t    ‖\033[0m");
        self::println("\033[32m ============================================\033[0m");
        echo PHP_EOL;
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
                break;
        }
        switch ($command[1]) {
            case 'start':
                if ($server->checkEnv()) {
                    return;
                }
                self::welcome(self::getProtocol($server->getSetting(), $command[0]));
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

    private static function getProtocol(array $config = [], string $name = 'http')
    {
        return "{$name}://{$config[$name]['host']}:{$config[$name]['port']}";
    }
}
