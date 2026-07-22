<?php

namespace Phiki\Ansi;

class IndexedAnsiColor extends AnsiColor
{
    public function __construct(
        public readonly int $index,
    ) {}

    public function resolve(AnsiPalette $palette): string
    {
        return $palette->indexToHex($this->index);
    }
}
