<?php

namespace Phiki\Theme;

readonly class TokenSettings
{
    public function __construct(
        public ?string $background,
        public ?string $foreground,
        public ?string $fontStyle,
    ) {}

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
