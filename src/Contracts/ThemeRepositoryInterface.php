<?php

namespace Phiki\Contracts;

interface ThemeRepositoryInterface
{
    /**
     * Get a theme from the repository.
     * 
     * If the theme is not already loaded, it will be loaded and cached.
     * 
     * @param string $name The name of the theme.
     * @return array
     * @throws \Phiki\Exceptions\UnrecognisedThemeException If the theme is not registered.
     */
    public function get(string $name): array;

    /**
     * Check whether a theme exists in the repository.
     * 
     * @param string $name The name of the theme.
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Register a new theme to use when highlighting.
     * 
     * @param string $name The name of the theme.
     * @param string|array $pathOrTheme The path to the theme file or the theme itself.
     * @return void
     */
    public function register(string $name, string|array $pathOrTheme): void;
}