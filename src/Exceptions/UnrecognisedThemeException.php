<?php

namespace Phiki\Exceptions;

use Exception;

class UnrecognisedThemeException extends Exception
{
    public static function make(string $name): self
    {
        return new self("The theme [{$name}] has not been registered.");
    }
}
