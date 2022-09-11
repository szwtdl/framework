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

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Swoole\Http\Request;
use Swoole\Http\Response;
use function FastRoute\simpleDispatcher;

class Route
{
    private static $instance;

    private static $routes = [];

    private static $dispatcher;

    private static array $middlewares = ['web'];

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
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response->header('Content-Type', 'text/plain');
//                $response->status(404);
                $response->end('<h1>Hello Swoole</h1>');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response->end('method ' . $httpMethod . ' empty');
                break;
            case Dispatcher::FOUND:
                $response->header('Content-Type', 'text/plain');
                $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
                break;
        }
    }

}
