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

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Swoole\Http\Request;
use Swoole\Http\Response;
use RuntimeException;
use function FastRoute\simpleDispatcher;

class Route
{
    private static $instance;

    private static $routes;

    private static $config;

    private static $dispatcher;

    private static $middlewares;

    private function __construct()
    {
    }


    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$middlewares = config('middleware');
            self::$config = config('config', []);
            self::$routes = config('routes', []);
            self::$dispatcher = simpleDispatcher(function (RouteCollector $routerCollector) {
                foreach (self::$routes as $routerDefine) {
                    if (!is_array($routerDefine) || count($routerDefine) < 2) {
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
        $args = [
            $request,
            $response,
        ];
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response->setHeader('Access-Control-Allow-Origin', '*');
                $response->setHeader('Access-Control-Expose-Headers', '*');
                $response->setHeader('Access-Control-Allow-Headers', '*');
                $response->status(404);
                $response->end("{$uri} Not defined");
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response->setHeader('Access-Control-Allow-Origin', '*');
                $response->setHeader('Access-Control-Expose-Headers', '*');
                $response->setHeader('Access-Control-Allow-Headers', '*');
                $response->end('method ' . $httpMethod . ' empty');
                break;
            case Dispatcher::FOUND:
                if (is_string($routeInfo[1])) {
                    [$className, $action] = explode('@', $routeInfo[1]);
                    if (!class_exists($className) || !method_exists((new $className), $action)) {
                        $response->end("Route class {$className}->{$action} not");
                        break;
                    }
                    //判断路由参数
                    if (is_array($routeInfo[2])) {
                        foreach ($routeInfo[2] as $item) {
                            $args[count($args) + 1] = is_numeric($item) ? (int)$item : $item;
                        }
                    }
                    $middlewares = [];
                    //筛选中间件
                    foreach (self::$routes as $route) {
                        if ($route[2] == $routeInfo[1] && isset($route[3])) {
                            if (!is_array($route[3])) {
                                continue;
                            }
                            $tmp = array_values($route[3]);
                            foreach ($tmp as $value) {
                                if (!isset(self::$middlewares[$value])) {
                                    continue;
                                }
                                foreach (self::$middlewares[$value] as $middleware) {
                                    $middlewares[] = $middleware;
                                }
                            }
                        }
                    }
                    $middlewareHandler = function ($request, $response) {
                    };
                    //如果中间件存在就挨个执行中间件
                    if (!empty($middlewares) && is_array($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $handle = (new $middleware)->handle($request, $response, $middlewareHandler);
                            if (is_array($handle) && $response->isWritable()) {
                                $response->setHeader('Access-Control-Allow-Origin', '*');
                                $response->setHeader('Access-Control-Expose-Headers', '*');
                                $response->setHeader('Access-Control-Allow-Headers', '*');
                                $response->setHeader('Content-Type', 'application/json; charset=UTF-8');
                                return $response->end(\json_encode($handle));
                            } elseif (is_string($handle) && $response->isWritable()) {
                                return $response->end($handle);
                            }
                        }
                    }
                    $middlewareHandler($request, $response);
                    $controller = new $className();
                    $result = $controller->{$action}(...$args);
                    if (is_array($result) && $response->isWritable()) {
                        $response->setHeader('Access-Control-Allow-Origin', '*');
                        $response->setHeader('Access-Control-Expose-Headers', '*');
                        $response->setHeader('Access-Control-Allow-Headers', '*');
                        $response->setHeader('Content-Type', 'application/json; charset=UTF-8');
                        $response->end(\json_encode($result));
                    } elseif (is_string($result) && $response->isWritable() || empty($result) && $response->isWritable()) {
                        $response->end($result);
                    }
                    return $response;
                }
                if (is_callable($routeInfo[1])) {
                    return call_user_func_array($routeInfo[1], [$request, $response, $routeInfo[2] ?? null]);
                }
                throw new RuntimeException("Route {$uri} error");
        }
        if ($response->isWritable()) {
            $response->status(400);
            $response->end("");
        }
    }
}
