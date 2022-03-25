<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://wiki.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */
namespace Szwtdl\Framework;

use Swoole\Exception;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Szwtdl\View\TwigEngine;

abstract class AbstractController
{
    protected array $configs;

    protected Response $response;

    protected Request $request;

    public function __construct()
    {
        try {
            $this->response = Context::get('response');
            $this->request = Context::get('request');
            $this->configs = config('config');
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @param array $data
     * @param int $code
     * @return Response
     */
    public function json(array $data = [],int $code = 200): Response
    {
        $this->response->header('Content-Type', 'application/json');
        $this->response->status($code);
        $this->response->end(\json_encode($data));
        return $this->response;
    }

    /**
     * @param string $template
     * @param array $data
     * @return Response
     */
    public function view(string $template, array $data = []): Response
    {
        try {
            $twig = (new TwigEngine())->render($template, $data, [
                'view_path' => VIEW_PATH,
                'cache_path' => RUNTIME_PATH . '/views',
            ]);
            $this->response->end($twig);
        } catch (Exception $exception) {
            $this->response->setStatusCode(200);
            $this->response->end($exception->getMessage());
        }
        return $this->response;
    }
}
