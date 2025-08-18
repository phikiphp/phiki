<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\HasContentNameInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Support\Regex;
use Phiki\Support\Str;

class BeginEndPattern implements PatternInterface, HasContentNameInterface
{
    public function __construct(
        public int $id,
        public Regex $begin,
        public Regex $end,
        public ?string $name,
        public ?string $contentName,
        public array $beginCaptures = [],
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
        return [
            [$this, $this->begin->get($allowA, $allowG)],
        ];
    }

    /**
     * Create the associated `EndPattern` for this `BeginEndPattern`.
     */
    public function createEndPattern(MatchedPattern $matched): EndPattern
    {
        return new EndPattern(
            id: $this->id,
            begin: $matched,
            end: $this->end,
            name: $this->name,
            contentName: $this->contentName,
            endCaptures: $this->endCaptures,
            captures: $this->captures,
            patterns: $this->patterns,
            injection: $this->injection,
        );
    }

    public function getId(): int
    {
        return $this->id;
    }
}
