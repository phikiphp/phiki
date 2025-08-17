<?php

namespace Phiki\Theme;

use Phiki\Support\Color;

class TokenSettings
{
    public function __construct(
        public ?string $background,
        public ?string $foreground,
        public ?string $fontStyle,
    ) {}

    /**
     * Take an array of token settings and flatten them into a single TokenSettings instance
     * without overriding values set by previous items in the array.
     * 
     * @param array<TokenSettings> $settings
     */
    public static function flatten(array $settings): TokenSettings
    {
        $flattened = [
            'background' => null,
            'foreground' => null,
            'fontStyle' => null,
        ];

        foreach ($settings as $setting) {
            if (! isset($flattened['background']) && isset($setting->background)) {
                $flattened['background'] = $setting->background;
            }

            if (! isset($flattened['foreground']) && isset($setting->foreground)) {
                $flattened['foreground'] = $setting->foreground;
            }

            if (! isset($flattened['fontStyle']) && isset($setting->fontStyle)) {
                $flattened['fontStyle'] = $setting->fontStyle;
            }
        }

        return new TokenSettings(
            $flattened['background'],
            $flattened['foreground'],
            $flattened['fontStyle']
        );
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
