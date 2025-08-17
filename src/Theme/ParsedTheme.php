<?php

namespace Phiki\Theme;

class ParsedTheme
{
    /**
     * @param array<string, string> $colors
     * @param TokenColor[] $tokenColors
     */
    public function __construct(
        public string $name,
        public array $colors = [],
        public array $tokenColors = [],
    ) {}
}
