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
}
