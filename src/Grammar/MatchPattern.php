<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Support\Regex;
use Phiki\Support\Str;

class MatchPattern implements PatternInterface
{
    /**
     * The compiled pattern for each combination of anchor flags, shared by every list it's compiled into.
     *
     * @var array<int, array{ 0: PatternInterface, 1: string }>
     */
    private array $compiled = [];

    /**
     * @param  Capture[]  $captures
     */
    public function __construct(
        public int $id,
        public Regex $match,
        public ?string $name,
        public array $captures = [],
        public bool $injection = false,
    ) {}

    public function getScopeName(array $captures): ?string
    {
        if ($this->name === null) {
            return null;
        }

        return Str::replaceScopeNameCapture($this->name, $captures);
    }

    /**
     * Compile the pattern into a list of matchable patterns.
     *
     * @return array<array{ 0: PatternInterface, 1: string }>
     */
    public function compile(ParsedGrammar $grammar, GrammarRepositoryInterface $grammars, bool $allowA, bool $allowG): array
    {
        return [
            $this->compiled[((int) $allowA << 1) | (int) $allowG] ??= [$this, $this->match->get($allowA, $allowG)],
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }
}
