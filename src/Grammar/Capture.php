<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Support\Str;

class Capture implements PatternInterface
{
    public function __construct(
        public string $index,
        public ?string $name,
        public array $patterns = [],
    ) {}

    public function retokenizeCapturedWithRule(): bool
    {
        return count($this->patterns) > 0;
    }

    public function getScopeName(array $captures): ?string
    {
        if ($this->name === null) {
            return null;
        }

        return Str::replaceScopeNameCapture($this->name, $captures);
    }

    public function compile(ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, bool $allowA, bool $allowG): array
    {
        $compiled = [];

        foreach ($this->patterns as $pattern) {
            $compiled = array_merge($compiled, $pattern->compile($grammar, $grammars, $allowA, $allowG));
        }

        return $compiled;
    }
}
