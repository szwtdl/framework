<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of wtdl-Shop.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/wtdl-swoole/wtdl/blob/master/LICENSE
 */
namespace Szwtdl\Framework\Server;

use Swoole\WebSocket\Server;
use Szwtdl\Framework\Application;
use Szwtdl\Framework\Listener;
use Szwtdl\Framework\Route;

class WebSocket
{
    protected $_server;

    protected $_config;

    protected $_route;

    public function __construct()
    {
        $config = config('servers');
        $wsConfig = $config['ws'];
        $this->_config = $wsConfig;
        $this->_server = new Server($wsConfig['host'], $wsConfig['port'], $config['mode']);
        $this->_server->set($wsConfig['settings']);
        if ($config['mode'] == SWOOLE_BASE) {
            $this->_server->on('managerStart', [$this, 'onMasterStart']);
        } else {
            $this->_server->on('start', [$this, 'onStart']);
        }
        $this->_server->on('workerStart', [$this, 'onWorkerStart']);
        if (! empty($wsConfig['callbacks'])) {
            foreach ($wsConfig['callbacks'] as $eventKey => $callbackItem) {
                [$class, $func] = $callbackItem;
                $this->_server->on($eventKey, [$class, $func]);
            }
        }
        if (isset($this->_config['process']) && ! empty($this->_config['process'])) {
            foreach ($this->_config['process'] as $processItem) {
                [$class, $func] = $processItem;
                $this->_server->addProcess($class::$func($this->_server));
            }
        }
        $this->_server->start();
    }

    public function onStart(\Swoole\Server $server)
    {
        Application::echoSuccess("Swoole WebSocket Server running：ws://{$this->_config['host']}:{$this->_config['port']}");
        Listener::getInstance()->listen('start', $server);
    }

    public function onManagerStart(\Swoole\Server $server)
    {
        Application::echoSuccess("Swoole WebSocket Server running：ws://{$this->_config['host']}:{$this->_config['port']}");
        Listener::getInstance()->listen('managerStart', $server);
    }

    public function onWorkerStart(\Swoole\Server $server, int $workerId)
    {
        $this->_route = Route::getInstance();
        Listener::getInstance()->listen('workerStart', $server, $workerId);
    }
}
