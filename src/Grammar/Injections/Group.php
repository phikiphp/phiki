<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Group implements InjectionMatcherInterface
{
    public function __construct(
        public Selector $child,
    ) {}

    public function matches(array $scopes): bool
    {
        return $this->child->matches($scopes);
    }
}