<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://wiki.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */

namespace Szwtdl\Framework\Server;

use Swoole\Server;
use Szwtdl\Framework\Application;
use Szwtdl\Framework\Listener;
use Szwtdl\Framework\Contract\ServerInterface;

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
        if (is_file($this->_config['http']['settings']['pid_file'])) {
            $this->master_pid = (int)file_get_contents($this->_config['http']['settings']['pid_file']);
        }
    }

    public function onStart(Server $server)
    {
        Application::echoSuccess("Swoole Http Server running：mqtt://{$this->_MqttConfig['host']}:{$this->_MqttConfig['port']}");
        Listener::getInstance()->listen('start', $server);
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
        if ($header['type'] == 1) {
            $resp = chr(32) . chr(2) . chr(0) . chr(0);
            $this->eventConnect($header, substr($data, 2));
            $server->send($fd, $resp);
        } elseif ($header['type'] == 3) {
            $offset = 2;
            $topic = $this->decodeString(substr($data, $offset));
            $offset += strlen($topic) + 2;
            $msg = substr($data, $offset);
            echo "client msg: {$topic}\n----------\n{$msg}\n";
        } elseif ($header['type'] == 8) {
            $resp = chr(32) . chr(2) . chr(0) . chr(0);
            $server->send($fd, $resp);
        }
        echo "received length=" . strlen($data) . "\n";
    }

    public function onClose($server, $fd)
    {
        echo "Client: Close.\n";
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
        foreach ($this->_MqttConfig['callbacks'] as $eventKey => $callbackItem) {
            [$class, $func] = $callbackItem;
            $this->_server->on($eventKey, [$class, $func]);
        }
        $this->_server->start();
    }


    public function reload()
    {

    }

    public function checkEnv()
    {
        return false;
    }

    public function stop()
    {

    }

    public function watch()
    {

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
        $offset += 1;
        $byte = ord($data[$offset]);
        $connect_info['willRetain'] = ($byte & 0x20 == 0x20);
        $connect_info['willQos'] = ($byte & 0x18 >> 3);
        $connect_info['willFlag'] = ($byte & 0x04 == 0x04);
        $connect_info['cleanStart'] = ($byte & 0x02 == 0x02);
        $offset += 1;
        $connect_info['keepalive'] = $this->decodeValue(substr($data, $offset, 2));
        $offset += 2;
        $connect_info['clientId'] = $this->decodeString(substr($data, $offset));
        return $connect_info;
    }
}
