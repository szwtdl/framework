<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */
namespace Szwtdl\Framework\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Wtdl\LogInterface;

class File implements LogInterface
{
    protected static $type = 'debug';

    public static function debug(string $message, $data = [])
    {
        $logs = new Logger(self::$type);
        $logs->pushHandler(new StreamHandler(BASE_PATH . '/runtime/logs/debug.log', Logger::DEBUG));
        $logs->debug($message, $data);
    }
}
