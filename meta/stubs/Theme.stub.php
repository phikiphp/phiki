<?php

namespace Phiki\Theme;

use Phiki\Contracts\ThemeRepositoryInterface;

enum Theme: string
{
    {cases}

    public function path(): string
    {
        return match ($this) {
            default => __DIR__ . "/../../resources/themes/{$this->value}.json",
        };
    }

    public function toParsedTheme(ThemeRepositoryInterface $repository): ParsedTheme
    {
        return $repository->get($this->value);
    }

    public static function parse(array $theme): ParsedTheme
    {
        return (new ThemeParser)->parse($theme);
    }
}
