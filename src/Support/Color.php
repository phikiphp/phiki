<?php

namespace Phiki\Support;

final class Color
{
    public const ANSI_RESET = "\033[0m";

    public const ANSI_BOLD = 1;

    public const ANSI_ITALIC = 3;

    public const ANSI_UNDERLINE = 4;

    public static function hexToAnsi(string $hex): int
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        [$r, $g, $b] = array_map(hexdec(...), str_split($hex, 2));

        return self::rgbToAnsi($r, $g, $b);
    }

    public static function rgbToAnsi(int $r, int $g, int $b): int
    {
        return intval(16 + (36 * round($r / 255 * 5)) + (6 * round($g / 255 * 5)) + round($b / 255 * 5));
    }
}
