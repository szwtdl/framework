<?php

declare(strict_types=1);
/**
 * 深圳网通动力网络技术有限公司
 * This file is part of wtdl-Shop.
 * @link     https://www.szwtdl.cn
 * @document https://doc.szwtdl.cn
 * @license  https://github.com/wtdl-swoole/wtdl/blob/master/LICENSE
 */
namespace Wtdl;

class Config
{
    private static $instance;

    private static $config = [];

    private function __construct()
    {
    }

    /**
     * @return Config
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $keys, $default = null)
    {
        $keys = explode('.', strtolower($keys));
        if (empty($keys)) {
            return null;
        }
        $file = array_shift($keys);
        if (empty(self::$config[$file])) {
            $filename = CONFIG_PATH . '/' . $file . '.php';
            if (! is_file($filename)) {
                return null;
            }
            self::$config[$file] = include $filename;
        }
        $config = self::$config[$file];
        while ($keys) {
            $key = array_shift($keys);
            if (! isset($config[$key])) {
                $config = $default;
                break;
            }
            $config = $config[$key];
        }
        return $config;
    }
}
