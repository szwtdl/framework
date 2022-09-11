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

namespace Framework;

class Application
{
    public const VERSION = '0.0.1';

    public static function println($strings)
    {
        echo $strings . PHP_EOL;
    }

    public static function welcome($host)
    {
        echo "\033[32m\t                   _      _ _ 
\t ___ ______      _| |_ __| | |
\t/ __|_  /\\ \\ /\\ / / __/ _` | |
\t\\__ \\/ /  \\ V  V /| || (_| | |
\t|___/___|  \\_/\\_/  \\__\\__,_|_|\033[0m\n";
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


    public function run()
    {
        global $argv;
        $count = count($argv);
        $funcName = $argv[$count - 1];
        $command = explode(':', $funcName);
        $serve = ServerFactory::getInstance($command[0]);
        switch ($command[0]) {
            case 'http':
                if (in_array($command[1], ['start', 'reload', 'stop'])) {
                    $serve->{$command[1]}();
                }
                break;
            case 'mqtt':
                if (in_array($command[1], ['start', 'reload'])) {
                    $serve->{$command[1]}();
                }
                break;
        }
    }
}
