<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of wtdl-Shop.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/wtdl-swoole/wtdl/blob/master/LICENSE
 */
namespace Tests;

use PHPUnit\Framework\TestCase;
use Wtdl\Request;

/**
 * @internal
 * @coversNothing
 */
class RequestTest extends TestCase
{
    public function testCreate()
    {
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/home/api-get';
        $_SERVER['HTTP_USER_AGENT'] = 'we7test-develop';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'zh-CN,zh;q=0.9,en;q=0.8';
        $request = new Request();
        $this->assertSame('we7test-develop', $request->getHeader('HTTP_USER_AGENT'));
    }
}
