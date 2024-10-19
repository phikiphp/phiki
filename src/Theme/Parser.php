<?php

namespace Phiki\Theme;

use Phiki\Support\Arr;

class Parser
{
    public function parse(array $theme): ParsedTheme
    {
        $name = $theme['name'];
        $colors = $theme['colors'];
        $tokenColors = Arr::filterMap($theme['tokenColors'] ?? [], function (array $tokenColor) {
            if (! isset($tokenColor['scope'])) {
                return null;
            }

            $scopes = Arr::wrap($tokenColor['scope']);

            return new TokenColor($scopes, new TokenSettings(
                $tokenColor['settings']['background'] ?? null,
                $tokenColor['settings']['foreground'] ?? null,
                $tokenColor['settings']['fontStyle'] ?? null,
            ));
        });

        return new ParsedTheme($name, $colors, $tokenColors);
    }
}
