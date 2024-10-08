<?php

namespace Phiki\Theme;

readonly class ThemeStyles
{
    protected ?string $backgroundColor;

    protected ?string $foregroundColor;

    protected array $tokenColors;

    public function __construct(ParsedTheme $theme)
    {
        $this->backgroundColor = $theme->colors['editor.background'] ?? null;
        $this->foregroundColor = $theme->colors['editor.foreground'] ?? null;

        /** @var array<string, array> */
        $tokenColors = [];

        foreach ($theme->tokenColors as $tokenColor) {
            $settings = $tokenColor->settings;

            foreach ($tokenColor->scopes as $scope) {
                $parts = explode('.', $scope);
                $current = &$tokenColors;

                foreach ($parts as $part) {
                    if (! isset($current[$part])) {
                        $current[$part] = [];
                    }

                    $current = &$current[$part];
                }

                $current['*'] = $settings;
            }
        }

        $this->tokenColors = $tokenColors;
    }

    public function base(): TokenSettings
    {
        return new TokenSettings(
            background: $this->backgroundColor,
            foreground: $this->foregroundColor,
            fontStyle: null,
        );
    }

    public function resolve(string $scope): ?TokenSettings
    {
        $parts = explode('.', $scope);
        $current = $this->tokenColors;
        $settings = null;

        foreach ($parts as $part) {
            // Can't find the right part here, break.
            if (! isset($current[$part])) {
                break;
            }

            $current = $current[$part];

            if (isset($current['*'])) {
                $settings = $current['*'];
            }
        }

        if (! $settings) {
            return null;
        }

        return $settings;
    }
}
