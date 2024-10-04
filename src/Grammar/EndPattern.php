<?php

namespace Phiki\Grammar;

use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Contracts\PatternCollectionInterface;
use Phiki\MatchedPattern;
use Phiki\Regex;
use Phiki\Tokenizer;

class EndPattern extends Pattern implements ContainsCapturesInterface, PatternCollectionInterface
{
    public function __construct(
        public MatchedPattern $begin,
        public Regex $end,
        public ?string $name,
        public ?string $contentName,
        public array $endCaptures = [],
        public array $captures = [],
        public array $patterns = [],
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

    public function getCaptures(): array
    {
        $captures = count($this->endCaptures) > 0 ? $this->endCaptures : $this->captures;

        return $captures;
    }

    public function hasCaptures(): bool
    {
        return count($this->endCaptures) > 0 || count($this->captures) > 0;
    }

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        $regex = preg_replace_callback('/\\\\(\d+)/', function ($matches) {
            return $this->begin->matches[$matches[1]][0] ?? $matches[0];
        }, $this->end->get());

        if (preg_match('/'.$regex.'/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
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
}
