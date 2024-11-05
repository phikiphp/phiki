<?php

namespace Phiki\Theme;

use Phiki\Support\Color;

readonly class TokenSettings
{
    public function __construct(
        public ?string $background,
        public ?string $foreground,
        public ?string $fontStyle,
    ) {}

    public function toAnsiEscape(): string
    {
        $codes = [];

        if (isset($this->background)) {
            $codes[] = Color::hexToAnsi($this->background) + 10;
        }

        if (isset($this->foreground)) {
            $codes[] = Color::hexToAnsi($this->foreground);
        }

        $fontStyles = explode(' ', $this->fontStyle ?? '');
        $decorations = [];

        foreach ($fontStyles as $fontStyle) {
            if ($fontStyle === 'underline') {
                $decorations[] = Color::ANSI_UNDERLINE;
            }

            if ($fontStyle === 'italic') {
                $decorations[] = Color::ANSI_ITALIC;
            }

            if ($fontStyle === 'bold') {
                $decorations[] = Color::ANSI_BOLD;
            }
        }

        return "\033[".implode(';', $decorations).';38;5;'.implode(';', $codes).'m';
    }

    public function toCssVarString(string $prefix): string
    {
        $styles = $this->toStyleArray();
        $vars = [];

        foreach ($styles as $property => $value) {
            $vars[] = "--phiki-{$prefix}-{$property}: {$value}";
        }

        return implode(';', $vars);
    }

    public function toStyleArray(): array
    {
        $styles = [];

        if (isset($this->background)) {
            $styles['background-color'] = $this->background;
        }

        if (isset($this->foreground)) {
            $styles['color'] = $this->foreground;
        }

        $fontStyles = explode(' ', $this->fontStyle ?? '');

        foreach ($fontStyles as $fontStyle) {
            if ($fontStyle === 'underline') {
                $styles['text-decoration'] = 'underline';
            }

            if ($fontStyle === 'italic') {
                $styles['font-style'] = 'italic';
            }

            if ($fontStyle === 'bold') {
                $styles['font-weight'] = 'bold';
            }

            if ($fontStyle === 'strikethrough') {
                $styles['text-decoration'] = 'line-through';
            }
        }

        return $styles;
    }

    public function toStyleString(): string
    {
        $styles = $this->toStyleArray();
        $styleString = '';

        foreach ($styles as $property => $value) {
            $styleString .= "{$property}: {$value};";
        }

        return $styleString;
    }
}
