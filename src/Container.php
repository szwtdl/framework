<?php

namespace Szwtdl\Framework;

use Psr\Container\ContainerInterface;
use Szwtdl\Framework\Exception\NotFoundException;
use Closure;

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
     * @param string $id
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
        } else {
            return false;
        }
    }
}