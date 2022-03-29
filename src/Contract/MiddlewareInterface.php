<?php

namespace Szwtdl\Framework\Contract;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface MiddlewareInterface
{
    public function execute(Request $request, Response $response, $next);
}