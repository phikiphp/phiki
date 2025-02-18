<?php

namespace Phiki\Grammar;

use Phiki\Grammar\Injections\Injection;

class MatchedInjection
{
    public function __construct(
        public Injection $injection,
        public MatchedPattern $matchedPattern,
    ) {}

    /**
     * Get the start position of the matched pattern.
     */
    public function offset(): int
    {
        return $this->matchedPattern->offset();
    }
}
