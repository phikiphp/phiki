<?php

namespace Phiki\Theme;

use Phiki\Support\Arr;

class Parser
{
    public function parse(array $theme): ParsedTheme
    {
        $name = $theme['name'];
        $colors = $theme['colors'];
        $tokenColors = array_map(function (array $tokenColors) {
            $scopes = Arr::wrap($tokenColors['scope']);

            return new TokenColor($scopes, new TokenSettings(
                $tokenColors['settings']['background'] ?? null,
                $tokenColors['settings']['foreground'] ?? null,
                $tokenColors['settings']['fontStyle'] ?? null,
            ));
        }, $theme['tokenColors'] ?? []);

        return new ParsedTheme($name, $colors, $tokenColors);
    }
}
