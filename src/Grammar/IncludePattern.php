<?php

namespace Phiki\Grammar;

use Phiki\Exceptions\UnrecognisedReferenceException;
use Phiki\MatchedPattern;
use Phiki\Tokenizer;

class IncludePattern extends Pattern
{
    public function __construct(
        public ?string $reference,
        public ?string $scopeName,
        public bool $injection = false,
    ) {}

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        $resolved = $tokenizer->resolve($this);

        if ($resolved !== null) {
            return $resolved->tryMatch($tokenizer, $lineText, $linePosition, $cannotExceed);
        }

        if ($tokenizer->isInStrictMode()) {
            throw UnrecognisedReferenceException::make($this->reference, $this->scopeName);
        }

        return false;
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

    public function scope(): null
    {
        return null;
    }
}
