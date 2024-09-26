<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Tokenizer;
use Phiki\MatchedPattern;

class EndPattern extends Pattern implements PatternCollectionInterface
{
    public function __construct(
        public string $end,
        public ?string $name,
        public ?string $contentName,
        public array $endCaptures = [],
        public array $captures = [],
        public array $patterns = [],
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
        return $this->name;
    }
}