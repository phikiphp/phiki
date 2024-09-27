<?php

namespace Phiki\Grammar;

use Phiki\MatchedPattern;
use Phiki\Tokenizer;

class IncludePattern extends Pattern
{
    public function __construct(
        public ?string $reference,
        public ?string $scopeName,
    ) {}

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        return $tokenizer->resolve($this)->tryMatch($tokenizer, $lineText, $linePosition, $cannotExceed);
    }

    public function isSelf(): bool
    {
        return $this->reference === '$self';
    }

    public function isBase(): bool
    {
        return $this->reference === '$base';
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getScopeName(): ?string
    {
        return $this->scopeName;
    }

    public function scope(): ?string
    {
        return null;
    }
}
