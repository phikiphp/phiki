<?php

namespace Phiki\Contracts;

use Phiki\Grammar\ParsedGrammar;

interface PatternInterface
{
    /**
     * Get the scope name of the pattern.
     * 
     * @param array<array{ 0: string, 1: int }> $captures
     */
    public function getScopeName(array $captures): ?string;

    /**
     * Compile the pattern into a list of matchable patterns.
     * 
     * @return array<array{ 0: PatternInterface, 1: string }>
     */
    public function compile(ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, bool $allowA, bool $allowG): array;
}
