<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Expression implements InjectionMatcherInterface
{
    public function __construct(
        public Filter|Group|Path $child,
        public Operator $operator = Operator::None,
        public bool $negated = false,
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
        $result = $this->child->matches($scopes);

        if ($this->negated) {
            return ! $result;
        }

        return $result;
    }
}
