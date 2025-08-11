<?php

namespace Phiki\Exceptions;

use Exception;

class GenericPatternException extends Exception
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public string $pattern,
        string $message,
        int $code = 0,
    ) {
        parent::__construct(
            <<<TXT
            {$message}

            The pattern that caused the issue was:
            {$pattern}
            TXT,
            $code
        );
    }
}
