<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;

class IncludePattern implements PatternInterface
{
    public function __construct(
        public ?string $reference,
        public ?string $scopeName,
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
        try {
            $resolved = match (true) {
                // "include": "$self"
                $this->reference === '$self' => $grammars->getFromScope($this->scopeName ?? $grammar->scopeName),
                // "include": "$base"
                $this->reference === '$base' => $grammar,
                // "include": "#name"
                $this->reference !== null && $this->scopeName === $grammar->scopeName => $grammar->resolve($this->reference),
                // "include": "scope#name"
                $this->reference !== null && $this->scopeName !== $grammar->scopeName => $grammars->getFromScope($this->scopeName)->resolve($this->reference),
                // "include": "scope"
                default => $grammars->getFromScope($this->scopeName),
            };
        } catch (UnrecognisedGrammarException) {
            $resolved = null;
        }

        if ($resolved === null) {
            return [];
        }

        return $resolved->compile($grammar, $grammars, $allowA, $allowG);
    }
}
