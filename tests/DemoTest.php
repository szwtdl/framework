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
namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DemoTest extends TestCase
{
    public function testRun()
    {
        $this->assertTrue(true);
    }

    public function testData(): array
    {
        $data = [];
        for ($i = 0; $i < 100; ++$i) {
            $data[$i] = mt_rand(1000, 9999);
        }
        $this->assertIsArray($data);
        return $data;
    }

    /**
     * @depends testData
     */
    public function testBox(array $data)
    {
        $this->assertSame(100, count($data));
    }
}
