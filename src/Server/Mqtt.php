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
use Swoole\Server;
use Swoole\Timer;
use Szwtdl\Framework\Contract\ServerInterface;
use Szwtdl\Framework\Listener;

class Mqtt implements ServerInterface
{
    protected $_server;

    protected $_config;

    protected $_MqttConfig;

    protected $master_pid;

    public function __construct()
    {
        $this->_config = config('servers');
        $this->_MqttConfig = $this->_config['mqtt'];
        if (is_file($this->_config['mqtt']['settings']['pid_file'])) {
            $this->master_pid = (int)file_get_contents($this->_config['mqtt']['settings']['pid_file']);
        }
    }

    public function onStart(Server $server)
    {
        Listener::getInstance()->listen('start', $server);
    }

    public function getSetting()
    {
        return $this->_config;
    }

    public function onManagerStart(Server $server)
    {
        Listener::getInstance()->listen('managerStart', $server);
    }

    public function onConnect($server, $fd)
    {
        echo "Client:Connect.\n";
    }

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        $header = $this->mqttGetHeader($data);
        var_dump($header);
        //mqtt 协议 3.1  type   1连接 | type 3发布 | type 8订阅 | type 14断开连接
        //mqtt 协议 3.1.1 type  1连接 | type 3发布 | type 8订阅 | type 12断开连接
        //mqtt 协议 5.0 type    1连接 | type 3发布 | type 8订阅 | type 12断开连接
        if ($header['type'] == 1) {
            $resp = chr(32) . chr(2) . chr(0) . chr(0);
            $this->eventConnect($header, substr($data, 2));
            $server->send($fd, $resp);
        } elseif ($header['type'] == 3) {
            $offset = 2;
            $topic = $this->decodeString(substr($data, $offset));
            $offset += strlen($topic) + 2;
            $msg = substr($data, $offset);
        } elseif ($header['type'] == 8) {
            $resp = chr(32) . chr(2) . chr(0) . chr(0);
            $server->send($fd, $resp);
        }
    }

    public function onClose($server, $fd)
    {
        echo "Client: Close.\n";
    }

    public function onWorkerStart(Server $server, int $workerId)
    {
        Listener::getInstance()->listen('workerStart', $server, $workerId);
    }

    public function onWorkerError(Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
        Listener::getInstance()->listen('workerError', $server, $worker_id, $worker_pid, $exit_code, $signal);
    }

    public function onShutdown(Server $server)
    {
        echo "===========onShutdown============\n";
        @unlink($this->_config['mqtt']['settings']['pid_file']);
        @unlink($this->_config['mqtt']['settings']['log_file']);
    }


    public function start()
    {
        $this->_server = new Server($this->_MqttConfig['host'], $this->_MqttConfig['port'], $this->_config['mode']);
        $this->_server->set($this->_MqttConfig['settings']);
        $this->_server->on('connect', [$this, 'onConnect']);
        $this->_server->on('receive', [$this, 'onReceive']);
        $this->_server->on('close', [$this, 'onClose']);
        if ($this->_config['mode'] == SWOOLE_BASE) {
            $this->_server->on('managerStart', [$this, 'onManagerStart']);
        } else {
            $this->_server->on('start', [$this, 'onStart']);
        }
        $this->_server->on('workerStart', [$this, 'onWorkerStart']);
        $this->_server->on('workerError', [$this, 'onWorkerError']);
        $this->_server->on('shutdown', [$this, 'onShutdown']);
        foreach ($this->_MqttConfig['callbacks'] as $eventKey => $callbackItem) {
            [$class, $func] = $callbackItem;
            $this->_server->on($eventKey, [$class, $func]);
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

    public function checkEnv()
    {
        if (!empty($this->master_pid)) {
            return true;
        }
        return false;
    }

    public function stop()
    {
        Timer::after(100, function () {
            System::exec('kill -TERM ' . $this->master_pid);
            unlink($this->_config['mqtt']['settings']['log_file']);
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

    public function decodeValue($data)
    {
        return 256 * ord($data[0]) + ord($data[1]);
    }

    public function decodeString($data)
    {
        $length = $this->decodeValue($data);
        return substr($data, 2, $length);
    }

    public function mqttGetHeader($data)
    {
        $byte = ord($data[0]);
        $header['type'] = ($byte & 0xF0) >> 4;
        $header['dup'] = ($byte & 0x08) >> 3;
        $header['qos'] = ($byte & 0x06) >> 1;
        $header['retain'] = $byte & 0x01;
        return $header;
    }

    public function eventConnect($header, $data)
    {
        $connect_info['protocol_name'] = $this->decodeString($data);
        $offset = strlen($connect_info['protocol_name']) + 2;
        $connect_info['version'] = ord(substr($data, $offset, 1));
        ++$offset;
        $byte = ord($data[$offset]);
        $connect_info['willRetain'] = ($byte & 0x20 == 0x20);
        $connect_info['willQos'] = ($byte & 0x18 >> 3);
        $connect_info['willFlag'] = ($byte & 0x04 == 0x04);
        $connect_info['cleanStart'] = ($byte & 0x02 == 0x02);
        ++$offset;
        $connect_info['keepalive'] = $this->decodeValue(substr($data, $offset, 2));
        $offset += 2;
        $connect_info['clientId'] = $this->decodeString(substr($data, $offset));
        return $connect_info;
    }
}
