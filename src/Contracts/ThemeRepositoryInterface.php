<?php

namespace Phiki\Contracts;

use Phiki\Theme\ParsedTheme;

interface ThemeRepositoryInterface
{
    /**
     * Get a theme from the repository.
     *
     * If the theme is not already loaded, it will be loaded and cached.
     *
     * @param  string  $name  The name of the theme.
     *
     * @throws \Phiki\Exceptions\UnrecognisedThemeException If the theme is not registered.
     */
    public function get(string $name): ParsedTheme;

    /**
     * Check whether a theme exists in the repository.
     *
     * @param  string  $name  The name of the theme.
     */
    public function has(string $name): bool;

    /**
     * Register a new theme to use when highlighting.
     *
     * @param  string  $name  The name of the theme.
     * @param  string|ParsedTheme  $pathOrTheme  The path to the theme file or the theme itself.
     */
    public function register(string $name, string|ParsedTheme $pathOrTheme): void;
}
