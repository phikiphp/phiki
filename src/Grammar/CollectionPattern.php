<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Tokenizer;

class CollectionPattern extends Pattern implements PatternCollectionInterface
{
    /**
     * @param  Pattern[]  $patterns
     */
    public function __construct(
        public array $patterns,
        public bool $injection = false,
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
        $closest = false;
        $offset = $linePosition;

        foreach ($this->getPatterns() as $pattern) {
            $matched = $pattern->tryMatch($tokenizer, $lineText, $linePosition, $cannotExceed);

            if ($matched === false) {
                continue;
            }

            if ($matched->offset() === $linePosition) {
                return $matched;
            }

            if ($closest === false) {
                $closest = $matched;
                $offset = $matched->offset();

                continue;
            }

            if ($matched->offset() < $offset) {
                $closest = $matched;
                $offset = $matched->offset();

                continue;
            }
        }

        return $closest;
    }

    public function scope(): null
    {
        return null;
    }

    public function wasInjected(): bool
    {
        return $this->injection;
    }

    public function __toString(): string
    {
        return sprintf('collection: count(%d)', count($this->getPatterns()));
    }
}
