<?php

namespace Szwtdl\Framework\Contract;

interface ServerInterface
{
    public function start();
    public function reload();
    public function checkEnv();
    public function stop();
    public function watch();
}