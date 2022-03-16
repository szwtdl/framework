<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of wtdl-Shop.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/wtdl-swoole/wtdl/blob/master/LICENSE
 */
use Wtdl\Context;

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
        return getInstance('\Wtdl\Config')->get($name, $default);
    }
}

if (! function_exists('shop_error')) {
    function shop_error()
    {
        try {
            echo "xxxx shop error\n";
        } catch (Throwable $exception) {
            $response = Context::get('response');
            $response->end('error shop');
            return $response;
        }
    }
}
