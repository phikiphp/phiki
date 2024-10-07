<?php

namespace Phiki\Theme;

readonly class TokenSettings
{
    public function __construct(
        public ?string $background,
        public ?string $foreground,
        public ?string $fontStyle,
    ) {}

    public function toStyleString(): string
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

        $styleString = '';

        foreach ($styles as $property => $value) {
            $styleString .= "{$property}: {$value};";
        }

        return $styleString;
    }
}
