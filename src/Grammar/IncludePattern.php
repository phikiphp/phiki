<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;

class IncludePattern implements PatternInterface
{
    public function __construct(
        public int $id,
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
        return $this->resolve($grammar, $grammars)?->compile($grammar, $grammars, $allowA, $allowG) ?? [];
    }

    /**
     * Resolve the pattern this include refers to, or `null` when it doesn't exist.
     */
    public function resolve(ParsedGrammar $grammar, GrammarRepositoryInterface $grammars): ?PatternInterface
    {
        try {
            return match (true) {
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
            return null;
        }
    }

    /**
     * Whether every one of the given patterns is missing, which is how vscode-textmate decides to leave out a
     * begin/end or begin/while rule: a rule whose patterns all include rules or grammars that don't exist is
     * left out, and so is a list of patterns that are all missing.
     *
     * @param  PatternInterface[]  $patterns
     * @param  array<int, true>  $visiting
     */
    public static function allMissing(array $patterns, ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, array $visiting = []): bool
    {
        if ($patterns === []) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (! self::missing($pattern, $grammar, $grammars, $visiting)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, true>  $visiting
     */
    private static function missing(PatternInterface $pattern, ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, array $visiting): bool
    {
        // A rule that is already being checked counts as present, as it does in vscode-textmate.
        if (isset($visiting[spl_object_id($pattern)])) {
            return false;
        }

        $visiting[spl_object_id($pattern)] = true;

        return match (true) {
            $pattern instanceof IncludePattern => ($resolved = $pattern->resolve($grammar, $grammars)) === null || self::missing($resolved, $grammar, $grammars, $visiting),
            $pattern instanceof CollectionPattern, $pattern instanceof ParsedGrammar, $pattern instanceof BeginEndPattern, $pattern instanceof BeginWhilePattern => self::allMissing($pattern->patterns, $grammar, $grammars, $visiting),
            default => false,
        };
    }

    public function getId(): int
    {
        return $this->id;
    }
}
