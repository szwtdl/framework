<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */
use Szwtdl\Framework\Context;

/**
 * @param mixed $data
 */
function dd($data)
{
    echo '<pre>';
    print_r($data);
}

if (! function_exists('getInstance')) {
    function getInstance($class)
    {
        return ($class)::getInstance();
    }
}

if (! function_exists('config')) {
    function config($name, $default = null)
    {
        return getInstance('\Szwtdl\Framework\Config')->get($name, $default);
    }
}

if (! function_exists('framework_error')) {
    function framework_error()
    {
        $response = Context::get('response');
        try {
            $response->status(500);
            $response->end('error 500');
        } catch (Throwable $exception) {
            $response->end('error Throwable' . $exception->getMessage());
        }
        return $response;
    }
}
