<?php

namespace Phiki;

readonly class ThemeStyles
{
    public string $name;

    public string $backgroundColor;

    public string $foregroundColor;

    public array $tokenColors;

    /**
     * @param  array{name: ?string, colors: array<string, string>, tokenColors: array<array{scope: string|array<string>, settings: array<string, string>}>|null}  $theme
     */
    public function __construct(array $theme)
    {
        $this->name = $theme['name'] ?? '';
        $this->backgroundColor = $theme['colors']['editor.background'];
        $this->foregroundColor = $theme['colors']['editor.foreground'];

        /** @var array<string, array> */
        $tokenColors = [];

        foreach ($theme['tokenColors'] ?? [] as $tokenColor) {
            $settings = $tokenColor['settings'];
            $scopes = Arr::wrap($tokenColor['scope']);

            foreach ($scopes as $scope) {
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

    public function baseTokenSettings(): TokenSettings
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

        return new TokenSettings(
            background: $settings['background'] ?? null,
            foreground: $settings['foreground'] ?? null,
            fontStyle: $settings['fontStyle'] ?? null,
        );
    }
}
