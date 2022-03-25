<?php

namespace Szwtdl\Framework\Contract;

interface DatabaseInterface
{
    public function connect();

    public function query();
}