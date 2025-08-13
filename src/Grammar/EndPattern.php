<?php

namespace Phiki\Grammar;

use Illuminate\Support\Str;
use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Support\Regex;

class EndPattern implements PatternInterface
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

    public function getScopeName(array $captures): ?string
    {
        if ($this->name === null) {
            return null;
        }

        return Str::replaceScopeNameCapture($this->name, $captures);
    }

    /**
     * Compile the pattern into a list of matchable patterns.
     * 
     * @return array<array{ 0: PatternInterface, 1: string }>
     */
    public function compile(ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, bool $allowA, bool $allowG): array
    {
        $compiled = [
            [$this, $this->end->get($allowA, $allowG)],
        ];

        foreach ($this->patterns as $pattern) {
            $compiled = array_merge($compiled, $pattern->compile($grammar, $grammars, $allowA, $allowG));
        }

        return $compiled;
    }   
}
