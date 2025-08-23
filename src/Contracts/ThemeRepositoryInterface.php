<?php

namespace Phiki\Contracts;

use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;

interface ThemeRepositoryInterface
{
    /**
     * Get a theme from the repository.
     *
     * If the theme is not already loaded, it will be loaded and cached.
     *
     * @throws \Phiki\Exceptions\UnrecognisedThemeException If the theme is not registered.
     */
    public function get(string $name): ParsedTheme;

    /**
     * Check whether a theme exists in the repository.
     */
    public function has(string $name): bool;

    /**
     * Register a new theme to use when highlighting.
     */
    public function register(string $name, string|ParsedTheme $pathOrTheme): void;

    /**
     * Resolve the given theme from the repository.
     */
    public function resolve(string|Theme|ParsedTheme $theme): ParsedTheme;
}
