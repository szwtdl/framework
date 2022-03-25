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

use Swoole\Coroutine;

class Context
{
    protected static $nonCoContext = [];

    public static function set(string $id, $value)
    {
        if (Coroutine::isCanceled()) {
            return Coroutine::getContext($id);
        }
        static::$nonCoContext[$id] = $value;
        return $value;
    }

    public static function get(string $id, $default = null)
    {
        if (Coroutine::isCanceled()) {
            return Coroutine::getContext($id);
        }
        return static::$nonCoContext[$id] ?? $default;
    }

    public static function destroy(string $id)
    {
        unset(static::$nonCoContext[$id]);
    }

    public static function inCoroutine(): bool
    {
        return Coroutine::getCid() > 0;
    }
}
