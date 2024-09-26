<?php

namespace Phiki\Grammar;

use Phiki\Tokenizer;
use Phiki\MatchedPattern;

class MatchPattern extends Pattern
{
    /**
     * @param string $match
     * @param string|null $name
     * @param Capture[] $captures
     */
    public function __construct(
        public string $match,
        public ?string $name,
        public array $captures = [],
    ) {}

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        dd();
    }

    public function scope(): ?string
    {
        return $this->name;
    }
}