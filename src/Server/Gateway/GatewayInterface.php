<?php

namespace Szwtdl\Framework\Server\Gateway;

interface GatewayInterface
{
    public static function send(int $uid, array $data);

    public static function sendAll(string $message = '');

    public static function isOnline(int $fd): bool;

    public static function bindUid(int $fd, string $uid): bool;

    public static function unbindUid(int $fd, string $uid): bool;

    public static function sendToUid(string $uid, array $data);

    public static function getClientIdByUid(string $fd);

    public static function sendToGroup(array $groups = [], string $message = '', array $exclude_client_id = []);
}