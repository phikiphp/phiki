<?php

namespace Phiki\Exceptions;

use Exception;

class MissingRequiredGrammarKeyException extends Exception
{
    public static function make(string $key): self
    {
        return new self("The grammar key [{$key}] is missing from the grammar array.");
    }
}
