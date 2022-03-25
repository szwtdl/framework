<?php

declare(strict_types=1);

/**
 * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://wiki.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */

use Swoole\Exception;
use Swoole\Coroutine;
use Szwtdl\Framework\Context;
use Swoole\Http\Response;
use Szwtdl\View\TwigEngine;

/**
 * @param mixed $data
 */
function dd($data)
{
    echo '<pre>';
    print_r($data);
}

if (!function_exists('getInstance')) {
    function getInstance($class)
    {
        return ($class)::getInstance();
    }
}

if (!function_exists('config')) {
    function config($name, $default = null)
    {
        return getInstance('\Szwtdl\Framework\Config')->get($name, $default);
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): Response
    {
        $response = Context::get('response');
        if (!empty($response) && $response->isWritable() && $response instanceof Response) {
            $response->setHeader("Content-Type", "text/html;charset=UTF-8");
            $response->setStatusCode(200);
            try {
                $twig = (new TwigEngine())->render($template, $data, [
                    'view_path' => VIEW_PATH,
                    'cache_path' => RUNTIME_PATH . '/views',
                ]);
                $response->end($twig);
            } catch (Exception $exception) {
                $response->end($exception->getMessage());
            }
        }
        return $response;
    }
}

if (!function_exists('json')) {
    function json(array $data = [], int $code = 200)
    {
        $response = Context::get('response');
        if (!empty($response) && $response->isWritable() && $response instanceof Response) {
            $response->setHeader("Content-Type", "application/json;charset=UTF-8");
            $response->setStatusCode($code);
            $response->end(\json_encode($data));
        }
        return $response;
    }
}

if (!function_exists('framework_error')) {
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

if (!function_exists('read_file')) {
    function read_file($dir, &$data = [])
    {
        if (!is_dir($dir)) {
            return false;
        }
        $handle = opendir($dir);
        if ($handle) {
            while (($fl = readdir($handle)) !== false) {
                $temp = $dir . DIRECTORY_SEPARATOR . $fl;
                // 如果不加  $fl!='.' && $fl != '..'  则会造成把$dir的父级目录也读取出来
                if (is_dir($temp) && $fl != '.' && $fl != '..') {
                    read_file($temp, $data);
                } else {
                    if ($fl != '.' && $fl != '..') {
                        $data[count($data)] = $temp;
                    }
                }
            }
        }
    }
}
