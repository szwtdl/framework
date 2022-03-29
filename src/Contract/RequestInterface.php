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
namespace Szwtdl\Framework\Contract;

use Psr\Http\Message\RequestInterface as MessageRequestInterface;

interface RequestInterface extends MessageRequestInterface
{
    public function input($key, $default = null);

    public function row($key);

    public function all();
}
