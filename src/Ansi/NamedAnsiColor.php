<?php

namespace Phiki\Ansi;

class NamedAnsiColor extends AnsiColor
{
    public function __construct(
        public readonly string $name,
    ) {}

    public function resolve(AnsiPalette $palette): string
    {
        return $palette->get($this->name);
    }
}
