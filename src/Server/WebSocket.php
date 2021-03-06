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
use Swoole\Server as SwServer;
use Swoole\Timer;
use Swoole\Event;
use Swoole\WebSocket\Server;
use Szwtdl\Framework\Contract\ServerInterface;
use Szwtdl\Framework\Listener;
use Szwtdl\Framework\Route;

class WebSocket implements ServerInterface
{
    protected $_server;

    protected $_config;

    protected $_wsConfig;

    protected $_route;

    protected $master_pid;

    public function __construct()
    {
        $config = config('servers');
        $this->_wsConfig = $config['ws'];
        if (is_file($config['ws']['settings']['pid_file'])) {
            $this->master_pid = (int)file_get_contents($config['ws']['settings']['pid_file']);
        }
        $this->_config = $config;
    }

    public function getSetting()
    {
        return $this->_config;
    }

    public function onStart(SwServer $server)
    {
        Listener::getInstance()->listen('start', $server);
    }

    public function onManagerStart(SwServer $server)
    {
        Listener::getInstance()->listen('managerStart', $server);
    }

    public function onWorkerStart(SwServer $server, int $workerId)
    {
        $this->_route = Route::getInstance();
        Listener::getInstance()->listen('workerStart', $server, $workerId);
    }

    public function onShutdown(SwServer $server)
    {
        echo "===========onShutdown============\n";
        @unlink($this->_config['ws']['settings']['pid_file']);
        @unlink($this->_config['ws']['settings']['log_file']);
    }

    public function start()
    {
        $this->_server = new Server($this->_wsConfig['host'], $this->_wsConfig['port'], $this->_config['mode']);
        $this->_server->table = swoole_table(10240, 'fd:int');
        $this->_server->set($this->_wsConfig['settings']);
        if ($this->_config['mode'] == SWOOLE_BASE) {
            $this->_server->on('managerStart', [$this, 'onMasterStart']);
        } else {
            $this->_server->on('start', [$this, 'onStart']);
        }
        $this->_server->on('workerStart', [$this, 'onWorkerStart']);
        $this->_server->on('shutdown', [$this, 'onShutdown']);
        if (!empty($this->_wsConfig['callbacks'])) {
            foreach ($this->_wsConfig['callbacks'] as $eventKey => $callbackItem) {
                [$class, $func] = $callbackItem;
                $this->_server->on($eventKey, [$class, $func]);
            }
        }
        if (isset($this->_wsConfig['process']) && !empty($this->_wsConfig['process'])) {
            foreach ($this->_wsConfig['process'] as $processItem) {
                [$class, $func] = $processItem;
                $this->_server->addProcess($class::$func($this->_server));
            }
        }
        $this->_server->start();
    }

    public function checkEnv()
    {
        if (!empty($this->master_pid)) {
            return true;
        }
        return false;
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
            @unlink($this->_config['ws']['settings']['log_file']);
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
                posix_kill($this->master_pid, SIGUSR1);
            }
        });
    }
}
