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

use Closure;
use Psr\Container\ContainerInterface;
use Szwtdl\Framework\Exception\NotFoundException;

class Container implements ContainerInterface
{
    protected $instances;

    protected $binds;

    public function bind($abstract, $concrete)
    {
        if ($concrete instanceof Closure) {
            $this->binds[$abstract] = $concrete;
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function make($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        array_unshift($parameters, $this);
        return call_user_func_array($this->binds[$abstract], $parameters);
    }

    /**
     * @return bool
     */
    public function get(string $id)
    {
        try {
            return isset($this->binds[$id]);
        } catch (NotFoundException $exception) {
            return $exception->getMessage();
        }
    }

    public function has(string $id): bool
    {
        if (isset($this->instances[$id])) {
            return true;
        }
        return false;
    }
}
