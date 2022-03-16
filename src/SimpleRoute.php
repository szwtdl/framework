<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of wtdl-Shop.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/wtdl-swoole/wtdl/blob/master/LICENSE
 */
namespace Wtdl;

use function FastRoute\simpleDispatcher;

class SimpleRoute
{
    private static $instance;

    private static $config;

    private static $dispatcher;

    private static $cache = [];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$config = Config::getInstance()->get('routes', []);
            self::$dispatcher = simpleDispatcher(
                function (\FastRoute\RouteCollector $routerCollector) {
                    foreach (self::$config as $routerDefine) {
                        $routerCollector->addRoute($routerDefine[0], $routerDefine[1], $routerDefine[2]);
                    }
                }
            );
        }
        return self::$instance;
    }

    public function dispatch($server, $fd, $data)
    {
        $first_line = \strstr($data, "\r\n", true);
        $tmp = \explode(' ', $first_line, 3);
        $method = $tmp[0] ?? 'GET';
        $uri = $tmp[1] ?? '/';
        $routeInfo = self::$dispatcher->dispatch($method, $uri);
        dd($routeInfo);
    }
}
