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

interface UploadsInterface
{
    public function getName(): string;

    public function setName(string $filename);

    public function getSize(): int;

    public function getExt();

    public function move(string $original, string $target);

    public function makeDirectory(string $path);
}
