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

namespace Szwtdl\Framework\Server;

use Swoole\Coroutine\System;
use Swoole\Event;
use Swoole\Exception;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Server as HttpServer;
use Swoole\Timer;
use Szwtdl\Framework\Context;
use Szwtdl\Framework\Contract\ServerInterface;
use Szwtdl\Framework\Listener;
use Szwtdl\Framework\Route;
use Szwtdl\Framework\SimpleRoute;

class Http implements ServerInterface
{
    protected $_server;

    protected $_config;

    protected $_httpConfig;

    protected $_route;

    protected $master_pid;

    public function __construct()
    {
        @session_start();
        $config = config('servers');
        $this->_httpConfig = $config['http'];
        if (is_file($config['http']['settings']['pid_file'])) {
            $this->master_pid = (int)file_get_contents($config['http']['settings']['pid_file']);
        }
        $this->_config = $config;
    }

    public function getSetting()
    {
        return $this->_config;
    }

    /**
     * @throws \Exception
     */
    public function onStart(HttpServer $server)
    {
        Listener::getInstance()->listen('start', $server);
    }

    /**
     * @throws \Exception
     */
    public function onManagerStart(HttpServer $server)
    {
        cli_set_process_title('php bin/wtdl http:start: master');
        Listener::getInstance()->listen('managerStart', $server);
    }

    /**
     * @throws \Exception
     */
    public function onWorkerStart(HttpServer $server, int $workerId)
    {
        $this->_route = Route::getInstance();
        Listener::getInstance()->listen('workerStart', $server, $workerId);
    }

    /**
     * @throws \Exception
     */
    public function onWorkerError(HttpServer $server, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
        Listener::getInstance()->listen('workerError', $server, $worker_id, $worker_pid, $exit_code, $signal);
    }

    /**
     * @throws \Exception
     */
    public function onSimpleWorkerStart(HttpServer $server, int $workerId)
    {
        $this->_route = SimpleRoute::getInstance();
        Listener::getInstance()->listen('simpleWorkerStart', $server, $workerId);
    }

    public function onShutdown(HttpServer $server)
    {
        echo "===========onShutdown============\n";
        @unlink($this->_config['http']['settings']['pid_file']);
        @unlink($this->_config['http']['settings']['log_file']);
    }

    /**
     * @throws Exception
     */
    public function onRequest(Request $request, Response $response)
    {
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $response->end();
            return;
        }
        //异常处理
        register_shutdown_function(function () use ($response) {
            $error = error_get_last();
            switch ($error['type'] ?? null) {
                case E_ERROR :
                case E_PARSE :
                case E_CORE_ERROR :
                case E_COMPILE_ERROR :
                    $response->status(500);
                    $response->end($error['message']);
                    break;
            }
            exit(0);
        });
        try {
            Context::set('request', $request);
            Context::set('response', $response);
            $this->_route->dispatch($request, $response);
        } catch (\Throwable $exception) {
            return $response->end($exception->getMessage());
        }
    }

    /**
     * @param $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive($server, $fd, $from_id, $data)
    {
        $this->_route->dispatch($server, $fd, $data);
    }

    public function checkEnv()
    {
        if (!empty($this->master_pid)) {
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
            $this->_server = new Server($this->_httpConfig['host'], $this->_httpConfig['port'], $this->_config['mode'], $this->_httpConfig['sock_type']);
            $this->_server->on('workerStart', [$this, 'onWorkerStart']);
            $this->_server->on('workerError', [$this, 'onWorkerError']);
            $this->_server->on('shutdown', [$this, 'onShutdown']);
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
            System::exec('kill -USR1 ' . $this->master_pid);
        });
        Event::wait();
    }

    public function stop()
    {
        Timer::after(100, function () {
            System::exec('kill -TERM ' . $this->master_pid);
            unlink($this->_config['http']['settings']['log_file']);
        });
        Event::wait();
    }

    public function watch()
    {
        $init = \inotify_init();
        $files = [];
        read_file(dirname(APP_PATH . DIRECTORY_SEPARATOR . 'App'), $files);
        read_file(dirname(CONFIG_PATH . DIRECTORY_SEPARATOR . 'config'), $files);
        $files = array_merge_recursive(get_included_files(), $files);
        foreach ($files as $file) {
            inotify_add_watch($init, $file, IN_MODIFY);
        }
        swoole_event_add($init, function ($fd) {
            $events = \inotify_read($fd);
            if (!empty($events)) {
                @posix_kill($this->master_pid, SIGUSR1);
            }
        });
    }
}
