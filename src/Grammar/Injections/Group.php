<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Group implements InjectionMatcherInterface
{
    public function __construct(
        public Selector $child,
    ) {}

    public function getPrefix(array $scopes): ?Prefix
    {
        if (! $this->matches($scopes)) {
            return null;
        }

        return $this->child->getPrefix($scopes);
    }

    public function matches(array $scopes): bool
    {
        return $this->child->matches($scopes);
    }
}
