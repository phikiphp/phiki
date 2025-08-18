<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Support\Str;

class Capture implements PatternInterface
{
    public function __construct(
        public int $id,
        public string $index,
        public ?string $name,
        public CollectionPattern $pattern,
    ) {}

    public function retokenizeCapturedWithRule(): bool
    {
        return count($this->pattern->patterns) > 0;
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
        return $this->pattern->compile($grammar, $grammars, $allowA, $allowG);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
