<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\InjectionMatcherInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Grammar\ParsedGrammar;

class Injection implements PatternInterface, InjectionMatcherInterface
{
    public function __construct(
        public int $id,
        public Selector $selector,
        public PatternInterface $pattern,
    ) {}

    public function getSelector(): Selector
    {
        return $this->selector;
    }

    public function getPrefix(array $scopes): ?Prefix
    {
        return $this->selector->getPrefix($scopes);
    }

    public function matches(array $scopes): bool
    {
        return $this->selector->matches($scopes);
    }

    public function getScopeName(array $captures): ?string
    {
        return $this->pattern->getScopeName($captures);
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
