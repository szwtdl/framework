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

interface ConfigInterface
{
    public function has(string $key): bool;

    public function get(string $name, mixed $default = null): mixed;

    public function set(string $name, mixed $value = null): void;

    public function all();
}
