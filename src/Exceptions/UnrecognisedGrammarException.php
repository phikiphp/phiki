<?php

namespace Phiki\Exceptions;

use Exception;

class UnrecognisedGrammarException extends Exception
{
    public static function make(string $name): self
    {
        return new self("The grammar [{$name}] has not been registered.");
    }
}
