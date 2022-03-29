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
use function FastRoute\simpleDispatcher;

class Route
{
    private static $instance;

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
            self::$config = config('routes', []);
            self::$dispatcher = simpleDispatcher(function (\FastRoute\RouteCollector $routerCollector) {
                foreach (self::$config as $routerDefine) {
                    $routerCollector->addRoute($routerDefine[0], $routerDefine[1], $routerDefine[2]);
                }
            });
        }
        return self::$instance;
    }

    public function dispatch($request, $response)
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
                $response->status(404);
                $response->end("404 Not http://{$request->server['host']}{$uri}");
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response->end('method ' . $httpMethod . ' empty');
                break;
            case Dispatcher::FOUND:
                if (is_string($routeInfo[1])) {
                    [$className, $action] = explode('@', $routeInfo[1]);
                    if (!class_exists($className)) {
                        $response->end("Route class {$className} not");
                        break;
                    }
                    $controller = new $className();
                    if (!method_exists($controller, $action)) {
                        $response->end("Route class {$className}->{$action} not action");
                        break;
                    }
                    if (is_array($routeInfo[2])) {
                        foreach ($routeInfo[2] as $item) {
                            $args[count($args) + 1] = $item;
                        }
                    }
                    $middlewareHandler = function ($request, $response) use ($controller, $action, $args) {
                        $res = $controller->{$action}(...$args);
                        if (is_array($res) && $response->isWritable()) {
                            $response->setHeader('Content-Type', 'application/json;charset=UTF-8');
                            $response->end(json_encode($res));
                        }
                    };
                    $middlewares = [];
                    foreach (self::$config as $route) {
                        if ($route[2] == $routeInfo[1] && isset($route[3]) && is_array($route[3])) {
                            $tmp = array_values($route[3]);
                            foreach ($tmp as $value) {
                                foreach (self::$middlewares[$value] as $m) {
                                    $middlewares[] = $m;
                                }
                            }
                        }
                    }
                    if (!empty($middlewares) && is_array($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            (new $middleware)->execute($request, $response, $middlewareHandler);
                        }
                    }
                    return $middlewareHandler($request, $response);
                }
                if (is_callable($routeInfo[1])) {
                    return call_user_func_array($routeInfo[1], [$request, $response, $routeInfo[2] ?? null]);
                }
                break;
        }
        if ($response->isWritable()) {
            $response->status(400);
            $response->end("");
        }
    }
}
