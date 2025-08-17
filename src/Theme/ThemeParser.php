<?php

namespace Phiki\Theme;

use Phiki\Exceptions\InvalidThemeException;
use Phiki\Support\Arr;

class ThemeParser
{
    public function parse(array $theme): ParsedTheme
    {
        if (! isset($theme['name'])) {
            throw new InvalidThemeException("Themes must have a `name` property.");
        }

        $name = $theme['name'];

        if (! isset($theme['colors'])) {
            throw new InvalidThemeException("Theme `{$name}` must have a `colors` property.");
        }

        $colors = $theme['colors'];

        if (! isset($theme['tokenColors'])) {
            throw new InvalidThemeException("Theme `{$name}` must have a `tokenColors` property.");
        }

        $tokenColors = array_map(function (array $tokenColor) {
            $scope = [];

            foreach (Arr::wrap($tokenColor['scope'] ?? []) as $part) {
                // Remove leading and trailing whitespace and commas in case of author errors.
                $part = trim(trim($part), ',');

                // This is the common case for a single scope without any AND or OR operators.
                if (! str_contains($part, ' ') && ! str_contains($part, ',')) {
                    $scope[] = new Scope([$part]);
                    continue;
                }

                $parts = array_map(fn (string $part) => trim($part), explode(',', $part));

                foreach ($parts as $part) {
                    $scope[] = new Scope(array_map(fn (string $p) => trim($p), explode(' ', $part)));
                }
            }

            return new TokenColor($scope, new TokenSettings(
                $tokenColor['settings']['background'] ?? null,
                $tokenColor['settings']['foreground'] ?? null,
                $tokenColor['settings']['fontStyle'] ?? null
            ));
        }, $theme['tokenColors']);

        return new ParsedTheme($name, $colors, $tokenColors);
    }
}
