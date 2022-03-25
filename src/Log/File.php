<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/framework.
 * @link     https://www.szwtdl.cn
 * @document https://wiki.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/framework/blob/master/LICENSE
 */

namespace Szwtdl\Framework\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class File implements LogInterface
{
    public static function debug(string $message, $data = [])
    {
        $logs = new Logger(self::$type);
        $logs->pushHandler(new StreamHandler(RUNTIME_PATH . '/logs/debug.log', Logger::DEBUG));
        $logs->debug($message, $data);
    }
}
