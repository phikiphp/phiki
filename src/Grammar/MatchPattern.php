<?php

namespace Phiki\Grammar;

use Exception;
use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Support\Regex;
use Phiki\Tokenizer;

class MatchPattern extends Pattern implements ContainsCapturesInterface
{
    /**
     * @param  Capture[]  $captures
     */
    public function __construct(
        public Regex $match,
        public ?string $name,
        public array $captures = [],
        public bool $injection = false,
    ) {}

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        try {
            if (preg_match('/'.$this->match->get($tokenizer->allowA(), $tokenizer->allowG()).'/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
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

    public function getCaptures(): array
    {
        return $this->captures;
    }

    public function hasCaptures(): bool
    {
        return count($this->captures) > 0;
    }

    public function scope(): ?array
    {
        return $this->name ? explode(' ', $this->name) : null;
    }

    public function wasInjected(): bool
    {
        return $this->injection;
    }

    public function __toString(): string
    {
        return sprintf('match: %s', $this->match);
    }
}
