<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;

class CollectionPattern implements PatternInterface
{
    /**
     * @param  PatternInterface[]  $patterns
     */
    public function __construct(
        public int $id,
        public array $patterns,
        public bool $injection = false,
    ) {}

    public function getScopeName(array $captures): ?string
    {
        return null;
    }

    /**
     * Compile the pattern into a list of matchable patterns.
     * 
     * @return array<array{ 0: PatternInterface, 1: string }>
     */
    public function compile(ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, bool $allowA, bool $allowG): array
    {
        $compiled = [];

        foreach ($this->patterns as $pattern) {
            $compiled = array_merge($compiled, $pattern->compile($grammar, $grammars, $allowA, $allowG));
        }

        return $compiled;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
