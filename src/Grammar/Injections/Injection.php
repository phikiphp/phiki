<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;
use Phiki\Contracts\PatternInterface;

class Injection implements InjectionMatcherInterface
{
    public function __construct(
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
}
