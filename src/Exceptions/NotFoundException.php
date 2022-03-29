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
namespace Szwtdl\Framework\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException implements NotFoundExceptionInterface
{
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    public function getMessage(): string
    {
        // TODO: Implement getMessage() method.
    }

    public function getCode()
    {
        // TODO: Implement getCode() method.
    }

    public function getFile(): string
    {
        // TODO: Implement getFile() method.
    }

    public function getLine(): int
    {
        // TODO: Implement getLine() method.
    }

    public function getTrace(): array
    {
        // TODO: Implement getTrace() method.
    }

    public function getTraceAsString(): string
    {
        // TODO: Implement getTraceAsString() method.
    }

    public function getPrevious()
    {
        // TODO: Implement getPrevious() method.
    }
}
