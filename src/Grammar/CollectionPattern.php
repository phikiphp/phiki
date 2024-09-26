<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Tokenizer;
use Phiki\MatchedPattern;

class CollectionPattern extends Pattern implements PatternCollectionInterface
{
    /**
     * @param Pattern[] $patterns
     */
    public function __construct(
        public array $patterns,
    ) {}

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    public function hasPatterns(): bool
    {
        return count($this->patterns) > 0;
    }

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        dd();
    }

    public function scope(): ?string
    {
        return null;
    }
}