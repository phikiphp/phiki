<?php

namespace Phiki\Theme;

use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Exceptions\UnrecognisedThemeException;

class ThemeRepository implements ThemeRepositoryInterface
{
    protected array $themes = [];

    public function __construct()
    {
        foreach (Theme::cases() as $theme) {
            $this->themes[$theme->value] = $theme->path();
        }
    }

    public function get(string $name): ParsedTheme
    {
        if (! $this->has($name)) {
            throw UnrecognisedThemeException::make($name);
        }

        $theme = $this->themes[$name];

        if ($theme instanceof ParsedTheme) {
            return $theme;
        }

        $parser = new ThemeParser;

        return $this->themes[$name] = $parser->parse(json_decode(file_get_contents($theme), true));
    }

    public function has(string $name): bool
    {
        return isset($this->themes[$name]);
    }

    public function register(string $name, string|ParsedTheme $pathOrTheme): void
    {
        $this->themes[$name] = $pathOrTheme;
    }

    public function resolve(string|Theme|ParsedTheme $theme): ParsedTheme
    {
        if ($theme instanceof ParsedTheme) {
            return $theme;
        }

        return match (true) {
            is_string($theme) => $this->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this),
        };
    }
}
