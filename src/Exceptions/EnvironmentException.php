<?php

namespace Phiki\Exceptions;

use Exception;

class EnvironmentException extends Exception
{
    public static function missingGrammarRepository(): static
    {
        return new static('The environment is missing a grammar repository.');
    }

    public static function missingThemeRepository(): static
    {
        return new static('The environment is missing a theme repository.');
    }
}
