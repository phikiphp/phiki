<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternCollectionInterface;
use Phiki\GrammarParser;
use Phiki\Tokenizer;
use Phiki\MatchedPattern;

final class Grammar extends Pattern implements PatternCollectionInterface
{
    /**
     * @param string $scopeName
     * @param Pattern[] $patterns
     * @param array<string, Pattern> $repository
     */
    public function __construct(
        public string $scopeName,
        public array $patterns,
        public array $repository,
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
        return $tokenizer->matchUsing($lineText, $this->getPatterns());
    }

    public function resolve(string $reference): ?Pattern
    {
        return $this->repository[$reference] ?? null;
    }

    public function scope(): ?string
    {
        return $this->scopeName;
    }

    public static function parse(array $grammar): static
    {
        $parser = new GrammarParser();

        return $parser->parse($grammar);
    }
}