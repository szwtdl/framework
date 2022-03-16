<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of wtdl-Shop.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/wtdl-swoole/wtdl/blob/master/LICENSE
 */
namespace Wtdl;

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
