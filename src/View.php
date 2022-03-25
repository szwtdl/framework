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

use Swoole\Http\Response;

abstract class View
{
    protected $vars = [];

    public function render(string $name, array $data = [])
    {
        $response = new Response();
        $filename = VIEW_PATH . '/' . $name;
        return $response->end(file_get_contents($filename));
    }

    public function assert(string $name, $value)
    {
        $this->vars[$name] = $value;
    }
}
