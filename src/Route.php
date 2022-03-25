<?php

declare(strict_types=1);
/**
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

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$config = Config::getInstance()->get('routes', []);
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
                $response->end("404 Not http://{$request->server['remote_addr']}:{$request->server['server_port']}{$uri}");
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response->end('method ' . $httpMethod . ' empty');
                break;
            case Dispatcher::FOUND:
                if (is_string($routeInfo[1])) {
                    [$className, $action] = explode('@', $routeInfo[1]);
                    if (! class_exists($className)) {
                        $response->status(404);
                        $response->end("Route class {$className} not");
                        break;
                    }
                    if (! method_exists(new $className(), $action)) {
                        $response->status(404);
                        $response->end("Route class {$className}->{$action} not action");
                        break;
                    }
                    if (is_array($routeInfo[2])) {
                        foreach ($routeInfo[2] as $key => $item) {
                            $args[count($args) + 1] = $item;
                        }
                    }
                    $controller = new $className();
                    $controller->{$action}(...$args);
                }
                if (is_callable($routeInfo[1])) {
                    return call_user_func_array($routeInfo[1], [$request, $response, $routeInfo[2] ?? null]);
                }
                break;
            default:
                $response->status(404);
                $response->end('');
        }
    }
}
