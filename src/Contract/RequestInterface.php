<?php

namespace Szwtdl\Framework\Contract;

use Psr\Http\Message\RequestInterface as MessageRequestInterface;

interface RequestInterface extends MessageRequestInterface
{
    public function input($key, $default = null);

    public function row($key);

    public function all();
}