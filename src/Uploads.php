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

use Szwtdl\Framework\Contract\UploadsInterface;

class Uploads implements UploadsInterface
{
    private $newfilename = null;
    protected $ext = null;
    protected $size = 0;

    public function __construct($data)
    {

    }

    public function getName(): string
    {
        return $this->newfilename ?? date('YmdHis') . mt_rand(1000, 10000);
    }

    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @param mixed $ext
     */
    public function setExt($ext): void
    {
        $this->ext = $ext;
    }

    public function setName(string $filename)
    {
        $this->newfilename = $filename;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function move(string $original, string $target)
    {

    }

    public function makeDirectory(string $path)
    {
        mkdir($path);
    }
}
