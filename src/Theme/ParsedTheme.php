<?php

namespace Phiki\Theme;

class ParsedTheme
{
    protected ThemeStyles $styles;

    /**
     * @param  array<string, string>  $colors
     * @param  array<int, TokenColor>  $tokenColors
     */
    public function __construct(
        public string $name,
        public array $colors,
        public array $tokenColors,
    ) {
        $this->styles = new ThemeStyles($this);
    }

    public function base(): TokenSettings
    {
        return $this->styles->base();
    }

    public function resolve(string $scope): ?TokenSettings
    {
        return $this->styles->resolve($scope);
    }

    public static function fromArray(array $theme): ParsedTheme
    {
        $parser = new Parser;

        return $parser->parse($theme);
    }
}
