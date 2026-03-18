<?php

namespace Phiki\Ansi;

use Phiki\Theme\ParsedTheme;

class AnsiPalette
{
    public const NAMED_COLORS = [
        'black',
        'red',
        'green',
        'yellow',
        'blue',
        'magenta',
        'cyan',
        'white',
        'brightBlack',
        'brightRed',
        'brightGreen',
        'brightYellow',
        'brightBlue',
        'brightMagenta',
        'brightCyan',
        'brightWhite',
    ];

    public const DEFAULTS = [
        'black' => '#000000',
        'red' => '#cd3131',
        'green' => '#0dbc79',
        'yellow' => '#e5e510',
        'blue' => '#2472c8',
        'magenta' => '#bc3fbc',
        'cyan' => '#11a8cd',
        'white' => '#e5e5e5',
        'brightBlack' => '#666666',
        'brightRed' => '#f14c4c',
        'brightGreen' => '#23d18b',
        'brightYellow' => '#f5f543',
        'brightBlue' => '#3b8eea',
        'brightMagenta' => '#d670d6',
        'brightCyan' => '#29b8db',
        'brightWhite' => '#ffffff',
    ];

    /**
     * @param  array<string, string>  $colors
     */
    public function __construct(
        protected array $colors,
        public readonly string $defaultForeground,
        public readonly string $defaultBackground,
    ) {}

    public static function fromTheme(ParsedTheme $theme): self
    {
        $colors = [];

        foreach (self::NAMED_COLORS as $name) {
            $key = 'terminal.ansi' . ucfirst($name);
            $colors[$name] = $theme->colors[$key] ?? self::DEFAULTS[$name];
        }

        return new self(
            $colors,
            $theme->colors['editor.foreground'] ?? '#ffffff',
            $theme->colors['editor.background'] ?? '#000000',
        );
    }

    public function get(string $name): string
    {
        return $this->colors[$name] ?? self::DEFAULTS[$name] ?? $this->defaultForeground;
    }

    public function indexToHex(int $index): string
    {
        // 0-15: Named colors
        if ($index < 16) {
            return $this->get(self::NAMED_COLORS[$index]);
        }

        // 16-231: 6x6x6 color cube
        if ($index < 232) {
            $index -= 16;
            $r = (int) ($index / 36);
            $g = (int) (($index % 36) / 6);
            $b = $index % 6;

            return sprintf(
                '#%02x%02x%02x',
                $r ? $r * 40 + 55 : 0,
                $g ? $g * 40 + 55 : 0,
                $b ? $b * 40 + 55 : 0,
            );
        }

        // 232-255: Grayscale ramp
        $gray = ($index - 232) * 10 + 8;

        return sprintf('#%02x%02x%02x', $gray, $gray, $gray);
    }

    public static function dimColor(string $hex): string
    {
        // Handle various hex formats and add 50% alpha
        if (preg_match('/^#([0-9a-f]{6})$/i', $hex, $matches)) {
            return '#' . $matches[1] . '80';
        }

        if (preg_match('/^#([0-9a-f]{8})$/i', $hex, $matches)) {
            // Already has alpha, reduce it by half
            $alpha = hexdec(substr($matches[1], 6, 2));
            $newAlpha = (int) ($alpha / 2);

            return '#' . substr($matches[1], 0, 6) . sprintf('%02x', $newAlpha);
        }

        if (preg_match('/^#([0-9a-f]{3})$/i', $hex, $matches)) {
            $r = $matches[1][0];
            $g = $matches[1][1];
            $b = $matches[1][2];

            return "#{$r}{$r}{$g}{$g}{$b}{$b}80";
        }

        return $hex;
    }
}
