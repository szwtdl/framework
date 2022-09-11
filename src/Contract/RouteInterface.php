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

namespace Framework\Contract;

interface RouteInterface
{
    public function get($url, $action);

    public function post($url, $action);

    public function any($url, $action);

    public function patch($url, $action);

    public function prefix($url);
}
