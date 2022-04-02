<?php

namespace Szwtdl\Framework\Server\Gateway;

use Swoole\Coroutine\Http\Client;
use Szwtdl\Framework\Exception\HttpException;

class Gateway implements GatewayInterface
{
    private static $client;

    public function __construct(string $host, int $port, string $path = '/')
    {
        try {
            self::$client = new Client($host, $port);
            self::$client->upgrade($path);
        } catch (\Exception $exception) {
            throw new HttpException($exception->getMessage());
        }
    }

    public static function send(int $fd, array $data)
    {
        try {
            return self::$client->push(\json_encode($data));
        } catch (\Exception $exception) {
            throw new \HttpException($exception->getMessage());
        }
    }

    public static function sendAll(string $message = '')
    {

    }

    public static function isOnline(int $fd): bool
    {
        // TODO: Implement isOnline() method.
    }

    public static function bindUid(int $fd, string $uid): bool
    {
        // TODO: Implement bindUid() method.
    }

    public static function unbindUid(int $fd, string $uid): bool
    {
        // TODO: Implement unbindUid() method.
    }

    public static function sendToUid(string $uid, array $data)
    {
        // TODO: Implement sendToUid() method.
    }

    public static function getClientIdByUid(string $fd)
    {
        // TODO: Implement getClientIdByUid() method.
    }

    public static function sendToGroup(array $groups = [], string $message = '', array $exclude_client_id = [])
    {
        // TODO: Implement sendToGroup() method.
    }
}