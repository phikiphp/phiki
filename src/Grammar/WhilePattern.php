<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Support\Regex;
use Phiki\Support\Str;

class WhilePattern implements PatternInterface
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

    public function getScopeName(array $captures): ?string
    {
        if ($this->name === null) {
            return null;
        }

        return Str::replaceScopeNameCapture($this->name, $captures);
    }

    public function captures(): array
    {
        return count(array_filter($this->whileCaptures)) > 0 ? $this->whileCaptures : $this->captures;
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
}
