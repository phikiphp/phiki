<?php

namespace Phiki\Grammar;

use Exception;
use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Contracts\ProvidesContentName;
use Phiki\Support\Regex;
use Phiki\Tokenizer;

class BeginWhilePattern extends Pattern implements ContainsCapturesInterface, PatternCollectionInterface, ProvidesContentName
{
    public function __construct(
        public Regex $begin,
        public Regex $while,
        public ?string $name,
        public ?string $contentName,
        public array $beginCaptures = [],
        public array $whileCaptures = [],
        public array $captures = [],
        public array $patterns = [],
        public bool $injection = false,
    ) {}

    public function getContentName(): ?string
    {
        return $this->contentName;
    }

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
        try {
            if (preg_match('/'.$this->begin->get($tokenizer->allowA(), $tokenizer->allowG()).'/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
                return false;
            }
        } catch (Exception) {
            return false;
        }

        if ($cannotExceed !== null && $matches[0][1] > $cannotExceed) {
            return false;
        }

        return new MatchedPattern($this, $matches);
    }

    public function scope(): ?array
    {
        return $this->name ? explode(' ', $this->name) : null;
    }

    public function hasCaptures(): bool
    {
        return count($this->beginCaptures) > 0 || count($this->captures) > 0;
    }

    public function getCaptures(): array
    {
        return count($this->beginCaptures) > 0 ? $this->beginCaptures : $this->captures;
    }

    public function createWhilePattern(MatchedPattern $self): WhilePattern
    {
        return new WhilePattern(
            $self,
            $this->while,
            $this->name,
            $this->contentName,
            $this->whileCaptures,
            $this->captures,
            $this->patterns,
        );
    }

    public function wasInjected(): bool
    {
        return $this->injection;
    }

    public function __toString(): string
    {
        return sprintf('begin: %s', $this->begin);
    }
}
