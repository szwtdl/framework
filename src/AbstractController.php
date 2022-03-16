<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */
namespace Szwtdl\Framework;

abstract class AbstractController
{
    protected $response;

    protected $request;

    public function __construct()
    {
        try {
            $this->response = Context::get('response');
            $this->request = Context::get('request');
        } catch (\Exception $exception) {
            print_r('Error' . $exception->getMessage());
        }
    }
}
