<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Filter implements InjectionMatcherInterface
{
    public function __construct(
        public Group|Path $child,
        public Prefix $prefix,
    ) {}

    public function getPrefix(array $scopes): ?Prefix
    {
        if (! $this->matches($scopes)) {
            return null;
        }

        return $this->prefix;
    }

    public function matches(array $scopes): bool
    {
        return $this->child->matches($scopes);
    }
}
