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

    public function match(array $scopes): ?TokenSettings
    {
        return null;
    }

    public function base(): TokenSettings
    {
        return new TokenSettings(
            $this->colors['editor.background'] ?? null,
            $this->colors['editor.foreground'] ?? null,
            null,
        );
    }
}
