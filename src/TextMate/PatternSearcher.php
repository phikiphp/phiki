<?php

namespace Phiki\TextMate;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Contracts\RegexEngineInterface;
use Phiki\Grammar\MatchedPattern;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Grammar\WhilePattern;

class PatternSearcher
{
    /**
     * Create a new instance.
     *
     * @param  array<array{ 0: PatternInterface, 1: string }>|null  $compiled  The pattern already compiled for these anchor flags, if the caller has it.
     * @param  SearchResultCache|null  $searches  Results of earlier searches on the same line, to reuse where they still hold.
     */
    public function __construct(
        protected PatternInterface $pattern,
        protected ParsedGrammar $grammar,
        protected GrammarRepositoryInterface $grammars,
        protected bool $allowA,
        protected bool $allowG,
        protected RegexEngineInterface $engine,
        protected ?array $compiled = null,
        protected ?SearchResultCache $searches = null,
    ) {}

    /**
     * Find the next closest match in the given text.
     */
    public function findNextMatch(string $lineText, int $linePos, bool $while = false): ?MatchedPattern
    {
        $patterns = $while && $this->pattern instanceof WhilePattern
            ? [
                [$this->pattern, $this->pattern->while->get($this->allowA, $this->allowG, $this->pattern->begin->matches)],
            ] : ($this->compiled ?? $this->pattern->compile($this->grammar, $this->grammars, $this->allowA, $this->allowG));

        return $this->engine->findNextMatch($patterns, $lineText, $linePos, $this->searches);
    }
}
