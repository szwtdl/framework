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

use Szwtdl\Db\QueryBuliderInterface;

abstract class Model implements QueryBuliderInterface
{
    public function first()
    {
    }

    public function all()
    {
    }

    public function update()
    {
    }

    public function delete()
    {
    }
}
