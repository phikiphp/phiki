<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;

final class ParsedGrammar implements PatternInterface
{
    /**
     * @param  PatternInterface[]  $patterns
     * @param  array<string, PatternInterface>  $repository
     * @param  Injections\Injection[]  $injections
     */
    public function __construct(
        public ?string $name,
        public string $scopeName,
        public array $patterns,
        public array $repository,
        public array $injections,
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

    /** @return Injections\Injection[] */
    public function getInjections(): array
    {
        return $this->injections;
    }

    public function hasInjections(): bool
    {
        return count($this->injections) > 0;
    }

    public function resolve(string $reference): ?PatternInterface
    {
        return $this->repository[$reference] ?? null;
    }

    public static function fromArray(array $grammar): ParsedGrammar
    {
        $parser = new Parser;

        return $parser->parse($grammar);
    }
}
