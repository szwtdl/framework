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
namespace Framework;

use Framework\Contract\ConfigInterface;

class Config implements \ArrayAccess, ConfigInterface
{
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function has(string $key): bool
    {
        // TODO: Implement has() method.
    }

    public function get(string $name, mixed $default = null): mixed
    {
        // TODO: Implement get() method.
    }

    public function set(string $name, mixed $value = null): void
    {
        // TODO: Implement set() method.
    }

    public function all()
    {
        // TODO: Implement all() method.
    }
}
