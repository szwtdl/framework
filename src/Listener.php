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

use Exception;

class Listener
{
    private static $instance;

    private static $config;

    private function __construct()
    {
    }

    /**
     * @return Listener
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
            self::$config = Config::getInstance()->get('listeners');
        }
        return self::$instance;
    }

    /**
     * @param $listener
     * @param ...$args
     * @return void
     * @throws Exception
     */
    public function listen($listener, ...$args)
    {
        $listeners = isset(self::$config[$listener]) ? self::$config[$listener] : [];
        while ($listeners) {
            [$class, $func] = array_shift($listeners);
            try {
                $class::getInstance()->{$func}(...$args);
            } catch (\Throwable $exception) {
                throw new Exception($exception->getMessage());
            }
        }
    }
}
