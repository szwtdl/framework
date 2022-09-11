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
return [
    'http' => [
        'host' => '0.0.0.0',
        'port' => 9501,
        'mode' => SWOOLE_PROCESS,
        'sock_type' => SWOOLE_SOCK_TCP,
        'callbacks' => [],
        'settings' => [
            'daemonize' => false,
            'worker_num' => swoole_cpu_num(),
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
        ]
    ],
    'mqtt' => [
        'host' => '0.0.0.0',
        'port' => 9503,
        'mode' => SWOOLE_PROCESS,
        'sock_type' => SWOOLE_SOCK_TCP,
        'callbacks' => [],
        'settings' => [

        ]
    ]
];
