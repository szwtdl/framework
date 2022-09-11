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

class ServerFactory
{
    protected static array $instances = [];

    private function __construct()
    {
    }

    public static function getInstance($server)
    {
        if (! isset(self::$instances[$server])) {
            $className = '\\Framework\\Server\\' . ucfirst($server);
            if (! class_exists($className)) {
                throw new \Exception('ClassName:' . $className);
            }
            self::$instances[$server] = new $className();
        }
        return self::$instances[$server];
    }
}
