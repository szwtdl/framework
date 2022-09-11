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
namespace Framework\Server;

use Framework\Route;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;

class Http
{
    protected $_route;

    protected HttpServer $http;

    protected array $options = [
        'host' => '0.0.0.0',
        'port' => 9501,
        'mode' => SWOOLE_PROCESS,
        'sock_type' => SWOOLE_SOCK_TCP,
        'callbacks' => [],
        'settings' => [
            'daemonize' => false,
            'reload_async' => true,
            'max_coroutine' => 10000,
            'trace_flags' => SWOOLE_TRACE_ALL,
            'log_level' => SWOOLE_LOG_TRACE,
            'log_date_format' => '%Y-%m-%d %H:%M:%S',
            'tcp_keepcount' => 3,
            'tcp_keepinterval' => 2,
            'tcp_user_timeout' => 5 * 1000, // 5秒
            'enable_static_handler' => true,
            'open_http_protocol' => true,
            'open_tcp_keepalive' => false,
            'open_http2_protocol' => true,
            'http_compression' => true,
            'http_compression_level' => 9,
            'package_max_length' => 50 * 1024 * 1024,
            'buffer_input_size' => 20 * 1024 * 1024,
            'buffer_output_size' => 50 * 1024 * 1024, // 必须为数字
        ],
    ];

    public function __construct(array $config = [])
    {
        $this->options = array_merge($this->options, $config);
        $this->http = new HttpServer($this->options['host'], $this->options['port'], $this->options['mode'], $this->options['sock_type']);
    }

    public function start()
    {
        $this->http->on('workerStart', [$this, 'onWorkerStart']);
        $this->http->on('workerError', [$this, 'onWorkerError']);
        $this->http->on('shutdown', [$this, 'onShutdown']);
        $this->http->on('request', [$this, 'onRequest']);
        $this->http->set($this->options['settings']);
        $this->http->start();
    }

    public function onWorkerStart(HttpServer $server, int $workerId)
    {
        $this->_route = Route::getInstance();
        echo "===========onWorkerStart======={$workerId}=====\n";
    }

    public function onWorkerError(HttpServer $server, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
        echo "===========onWorkerError============\n";
    }

    public function onShutdown(HttpServer $server)
    {
        echo "===========onShutdown============\n";
    }

    public function onRequest(Request $request, Response $response)
    {
        $response->header('Server', 'nginx/12.1');
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $response->end();
            return;
        }
        register_shutdown_function(function () use ($response) {
            $error = error_get_last();
            switch ($error['type'] ?? null) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                    $response->status(500);
                    $response->end($error['message']);
                    break;
            }
            exit(0);
        });
        $this->_route->dispatch($request, $response);
    }
}
