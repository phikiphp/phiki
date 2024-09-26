<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Tokenizer;
use Phiki\MatchedPattern;

class BeginEndPattern extends Pattern implements PatternCollectionInterface
{
    public function __construct(
        public string $begin,
        public string $end,
        public ?string $name,
        public ?string $contentName,
        public array $beginCaptures = [],
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
        $regex = $this->begin;

        if (preg_match('/' . str_replace('/', '\/', $regex) . '/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
            return false;
        }

        if ($cannotExceed !== null && $matches[0][1] > $cannotExceed) {
            return false;
        }

        return new MatchedPattern($this, $matches);
    }

    public function scope(): ?string
    {
        return $this->name;
    }

    public function hasBeginCaptures(): bool
    {
        return count($this->beginCaptures) > 0 || count($this->captures) > 0;
    }

    public function createEndPattern(): EndPattern
    {
        return new EndPattern(
            $this->end,
            $this->name,
            $this->contentName,
            $this->endCaptures,
            $this->captures,
            $this->patterns,
        );
    }
}