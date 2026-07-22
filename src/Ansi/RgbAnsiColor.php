<?php

namespace Phiki\Ansi;

class RgbAnsiColor extends AnsiColor
{
    public function __construct(
        public readonly int $r,
        public readonly int $g,
        public readonly int $b,
    ) {}

    public function resolve(AnsiPalette $palette): string
    {
        return sprintf('#%02x%02x%02x', $this->r, $this->g, $this->b);
    }
}
