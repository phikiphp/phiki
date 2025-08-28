<?php

namespace Phiki\Support;

/** @internal */
class Str
{
    const CAPTURING_REGEX_SOURCE = '/\$(\d+)|\${(\d+):\/(downcase|upcase)}/';

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

    public static function startsWithScope(string $subject, string $scope): bool
    {
        return $subject === $scope || str_starts_with($subject, trim($scope, '.').'.');
    }

    public static function dotCount(string $subject): int
    {
        return substr_count($subject, '.');
    }

    /**
     * Replace capture references in a scope name with their corresponding capture values.
     *
     * E.g. `storage.type.${1:/downcase}.php` -> `storage.type.const.php`
     */
    public static function replaceScopeNameCapture(string $scopeName, array $captures): string
    {
        return preg_replace_callback(self::CAPTURING_REGEX_SOURCE, function (array $matches) use ($captures) {
            $capture = $captures[intval($matches[1])];

            if (! $capture) {
                return $matches[0];
            }

            $result = $capture[0];

            if ($result === '') {
                return '';
            }

            while ($result && $result[0] === '.') {
                $result = substr($result, 1);
            }

            return match ($matches[3] ?? null) {
                'downcase' => strtolower($result),
                'upcase' => strtoupper($result),
                default => $result,
            };
        }, $scopeName);
    }
}
