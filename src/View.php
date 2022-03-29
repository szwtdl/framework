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

abstract class View
{

    public function render(string $name, array $data = [])
    {
        $response = new Response();
        $filename = VIEW_PATH . '/' . $name;
        return $response->end(file_get_contents($filename));
    }

}
