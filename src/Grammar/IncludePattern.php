<?php

namespace Phiki\Grammar;

use Phiki\Tokenizer;
use Phiki\MatchedPattern;

class IncludePattern extends Pattern
{
    public function __construct(
        public ?string $reference,
        public ?string $scopeName,
    ) {}

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        dd();
    }

    public function scope(): ?string
    {
        return null;
    }
}