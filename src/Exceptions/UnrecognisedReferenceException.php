<?php

namespace Phiki\Exceptions;

use Exception;

class UnrecognisedReferenceException extends Exception
{
    public static function make(string $reference, string $source): self
    {
        return new self("Unrecognised reference [{$reference}] in source [{$source}].");
    }
}
