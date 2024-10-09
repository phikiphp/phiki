<?php

namespace Phiki\Exceptions;

use Exception;

class EnvironmentException extends Exception
{
    public static function missingGrammarRepository(): self
    {
        return new self('The environment is missing a grammar repository.');
    }

    public static function missingThemeRepository(): self
    {
        return new self('The environment is missing a theme repository.');
    }
}
