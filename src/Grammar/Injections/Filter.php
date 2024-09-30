<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Filter implements InjectionMatcherInterface
{
    public function __construct(
        public Group|Path $child,
        public Prefix $prefix,
    ) {}

    public function matches(array $scopes): bool
    {
        dd();
    }
}