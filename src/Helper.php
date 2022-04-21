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
namespace Szwtdl\Framework;

use Swoole\Http\Response;
use Szwtdl\View\TwigEngine;

class Helper
{
    private static $instance;

    private function __construct()
    {
    }

    /**
     * @return Helper
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @return null|mixed|Response
     */
    public function json(array $data)
    {
        $response = Context::get('response');
        if (! empty($response) && $response->isWritable() && $response instanceof Response) {
            $response->setHeader('Content-Type', 'application/json;charset=UTF-8');
            $response->end(\json_encode($data));
        }
        return $response;
    }

    /**
     * @throws \Swoole\Exception
     * @return null|mixed|Response
     */
    public function view(string $filename, array $data = [])
    {
        $response = Context::get('response');
        if (! empty($response) && $response->isWritable() && $response instanceof Response) {
            $response->setHeader('Content-Type', 'text/html;charset=UTF-8');
            $response->setStatusCode(200);
            try {
                $twig = (new TwigEngine())->render($filename, $data, [
                    'view_path' => VIEW_PATH,
                    'cache_path' => RUNTIME_PATH . '/views',
                ]);
                $response->end($twig);
            } catch (\Exception $exception) {
                $response->end($exception->getMessage());
            }
        }
        return $response;
    }

    /**
     * @return null|mixed|Response
     */
    public function redirect(string $url, int $code = 302)
    {
        $response = Context::get('response');
        if (! empty($response) && $response->isWritable() && $response instanceof Response) {
            $response->redirect($url, $code);
        }
        return $response;
    }
}
