<?php

namespace Szwtdl\Framework;

interface UploadsInterface
{
    public function getName(): string;

    public function setName(string $filename);

    public function getSize(): int;

    public function move(string $original, string $target);

    public function makeDirectory(string $path);
}