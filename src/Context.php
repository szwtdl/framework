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
