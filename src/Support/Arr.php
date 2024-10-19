<?php

namespace Phiki\Support;

final class Arr
{
    public static function wrap(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return [$value];
    }

    public static function filterMap(array $array, callable $callback): array
    {
        return array_filter(array_map($callback, $array));
    }
}
