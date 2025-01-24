<?php

namespace Phiki\Support;

/** @internal */
class Str
{
    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    public static function trimFirst(string $subject, string $needle): string
    {
        if (str_starts_with($subject, $needle)) {
            return substr($subject, 1);
        }

        return $subject;
    }

    public static function trimLast(string $subject, string $needle): string
    {
        if (str_ends_with($subject, $needle)) {
            return substr($subject, 0, -1);
        }

        return $subject;
    }

    public static function trimOnce(string $subject, string $needle): string
    {
        return self::trimFirst(self::trimLast($subject, $needle), $needle);
    }
}
