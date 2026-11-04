<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\HasContentNameInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Support\Regex;
use Phiki\Support\Str;

class BeginWhilePattern implements HasContentNameInterface, PatternInterface
{
    /**
     * Whether every nested pattern includes a rule or grammar that doesn't exist.
     */
    private ?bool $missing = null;

    public function __construct(
        public int $id,
        public Regex $begin,
        public Regex $while,
        public ?string $name,
        public ?string $contentName,
        public array $beginCaptures = [],
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
        return count(array_filter($this->beginCaptures)) > 0 ? $this->beginCaptures : $this->captures;
    }

    public function getContentName(array $captures): ?string
    {
        if ($this->contentName === null) {
            return null;
        }

        return Str::replaceScopeNameCapture($this->contentName, $captures);
    }

    /**
     * Compile the pattern into a list of matchable patterns.
     *
     * @return array<array{ 0: PatternInterface, 1: string }>
     */
    public function compile(ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, bool $allowA, bool $allowG): array
    {
        // Like vscode-textmate, this is decided once, the first time the rule is compiled.
        if ($this->missing ??= IncludePattern::allMissing($this->patterns, $grammar, $grammars)) {
            return [];
        }

        return [
            [$this, $this->begin->get($allowA, $allowG)],
        ];
    }

    public function createWhilePattern(MatchedPattern $matched): WhilePattern
    {
        return new WhilePattern(
            $this->id,
            $matched,
            $this->while,
            $this->name,
            $this->contentName,
            $this->whileCaptures,
            $this->captures,
            $this->patterns,
            $this->injection
        );
    }

    public function getId(): int
    {
        return $this->id;
    }
}
