<?php

namespace Phiki\Grammar;

use Exception;
use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Contracts\ProvidesContentName;
use Phiki\Support\Regex;
use Phiki\Tokenizer;

class WhilePattern extends Pattern implements ContainsCapturesInterface, PatternCollectionInterface, ProvidesContentName
{
    public function __construct(
        public MatchedPattern $begin,
        public Regex $while,
        public ?string $name,
        public ?string $contentName,
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

    public function getCaptures(): array
    {
        $captures = count($this->whileCaptures) > 0 ? $this->whileCaptures : $this->captures;

        return $captures;
    }

    public function hasCaptures(): bool
    {
        return count($this->whileCaptures) > 0 || count($this->captures) > 0;
    }

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        $regex = preg_replace_callback('/\\\\(\d+)/', function ($matches) {
            if (! isset($this->begin->matches[$matches[1]][0])) {
                return $matches[0];
            }

            return preg_quote($this->begin->matches[$matches[1]][0], '/');
        }, $this->while->get($tokenizer->allowA(), $tokenizer->allowG()));

        try {
            if (preg_match('/'.$regex.'/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
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

    public function wasInjected(): bool
    {
        return $this->injection;
    }

    public function __toString(): string
    {
        return sprintf('while: %s', $this->while);
    }
}
