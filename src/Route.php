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

use FastRoute\RouteCollector;
use Swoole\Http\Request;
use Swoole\Http\Response;
use function FastRoute\simpleDispatcher;

class Route
{
    private static $instance;

    private static $routes = [];

    private static $config;

    private static $dispatcher;

    private static $middlewares = [];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$dispatcher = simpleDispatcher(function (RouteCollector $routerCollector) {
                foreach (self::$routes as $routerDefine) {
                    if (! is_array($routerDefine) || count($routerDefine) < 2) {
                        continue;
                    }
                    $routerCollector->addRoute($routerDefine[0], $routerDefine[1], $routerDefine[2]);
                }
            });
        }
        return self::$instance;
    }

    public function dispatch(Request $request, Response $response)
    {
        $httpMethod = $request->server['request_method'];
        $uri = $request->server['request_uri'] ?? '/';
        $routeInfo = self::$dispatcher->dispatch($httpMethod, $uri);
        dd($routeInfo);
    }
}
