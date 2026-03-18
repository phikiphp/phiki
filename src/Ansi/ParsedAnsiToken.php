<?php

namespace Phiki\Ansi;

class ParsedAnsiToken
{
    /**
     * @param  array<string>  $decorations
     */
    public function __construct(
        public readonly string $text,
        public readonly ?AnsiColor $foreground = null,
        public readonly ?AnsiColor $background = null,
        public readonly array $decorations = [],
    ) {}

    public function hasDecoration(string $decoration): bool
    {
        return in_array($decoration, $this->decorations, true);
    }
}
