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

trait Singleton
{
    private static $instance;

    /**
     * @param ...$args
     * @return static
     */
    public static function getInstance(...$args): static
    {
        if (! isset(self::$instance)) {
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }
}
