<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of wtdl-Shop.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/wtdl-swoole/wtdl/blob/master/LICENSE
 */

namespace Wtdl\Server;

use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Timer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Server as HttpServer;
use Wtdl\Application;
use Wtdl\Context;
use Wtdl\Listener;
use Wtdl\Route;
use Wtdl\SimpleRoute;

class Http
{
    protected $_server;

    protected $_config;
    protected $_httpConfig;
    protected $_route;

    public function __construct()
    {
        $config = config('servers');
        $this->_httpConfig = $config['http'];
        $this->_config = $config;
    }

    public function onStart(HttpServer $server)
    {
        Application::echoSuccess('-----------------ShopTo Version ' . Application::VERSION . '----------------------');
        Application::echoSuccess('-----------------Swoole Version ' . swoole_version() . '----------------------');
        Application::echoSuccess("Swoole Http Server running：http://{$this->_config['host']}:{$this->_config['port']}");
        Listener::getInstance()->listen('start', $server);
    }

    public function onManagerStart(HttpServer $server)
    {
        echo "xxxx{$workerId}xxxxxxxx\n";
        Listener::getInstance()->listen('managerStart', $server);
    }

    public function onWorkerStart(HttpServer $server, int $workerId)
    {
        var_dump(get_included_files());
        $this->_route = Route::getInstance();
        Listener::getInstance()->listen('workerStart', $server, $workerId);
    }

    public function onSimpleWorkerStart(HttpServer $server, int $workerId)
    {
        $this->_route = SimpleRoute::getInstance();
        Listener::getInstance()->listen('simpleWorkerStart', $server, $workerId);
    }

    public function onRequest(Request $request, Response $response)
    {
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $response->end();
            return;
        }
        Context::set('request', $request);
        Context::set('response', $response);
        $this->_route->dispatch($request, $response);
    }

    public function onReceive($server, $fd, $from_id, $data)
    {
        $this->_route->dispatch($server, $fd, $data);
    }

    public function checkEnv()
    {
        $master_pid = (int)@file_get_contents($this->_config['http']['settings']['pid_file']);
        if (!empty($master_pid)) {
            return true;
        }
        return false;
    }

    public function start()
    {
        if (isset($this->_httpConfig['settings']['only_simple_http'])) {
            $this->_server = new HttpServer($this->_httpConfig['host'], $this->_httpConfig['port'], $this->_config['mode']);
            $this->_server->on('workerStart', [$this, 'onSimpleWorkerStart']);
            $this->_server->on('receive', [$this, 'onReceive']);
            unset($this->_httpConfig['settings']['only_simple_http']);
        } else {
            $this->_server = new Server(
                $this->_httpConfig['host'],
                $this->_httpConfig['port'],
                $this->_config['mode'],
                $this->_httpConfig['sock_type']
            );
            $this->_server->on('workerStart', [$this, 'onWorkerStart']);
            $this->_server->on('request', [$this, 'onRequest']);
        }
        $this->_server->set($this->_httpConfig['settings']);
        if ($this->_config['mode'] == SWOOLE_BASE) {
            $this->_server->on('managerStart', [$this, 'onManagerStart']);
        } else {
            $this->_server->on('start', [$this, 'onStart']);
        }
        foreach ($this->_httpConfig['callbacks'] as $eventKey => $callbackItem) {
            [$class, $func] = $callbackItem;
            $this->_server->on($eventKey, [$class, $func]);
        }
        if (isset($this->_config['process']) && !empty($this->_config['process'])) {
            foreach ($this->_config['process'] as $processItem) {
                [$class, $func] = $processItem;
                $this->_server->addProcess($class::$func($this->_server));
            }
        }
        $this->_server->start();
    }

    public function reload()
    {
        Timer::after(100, function () {
            $master_pid = (int)file_get_contents($this->_config['http']['settings']['pid_file']);
            System::exec("kill -USR1 " . $master_pid);
        });
        \Swoole\Event::wait();
    }

    public function stop()
    {
        Timer::after(100, function () {
            $master_pid = (int)file_get_contents($this->_config['http']['settings']['pid_file']);
            System::exec("kill -TERM " . $master_pid);
        });
        \Swoole\Event::wait();
    }

    public function watch()
    {
        $init = \inotify_init();
        $files = get_included_files();
        foreach ($files as $file) {
            \inotify_add_watch($init, $file, IN_MODIFY);
        }
        swoole_event_add($init, function ($fd) {
            $events = \inotify_read($fd);
            if (!empty($events)) {
                $master_pid = (int)file_get_contents($this->_config['http']['settings']['pid_file']);
                posix_kill($master_pid, SIGUSR1);
            }
        });
    }

}
