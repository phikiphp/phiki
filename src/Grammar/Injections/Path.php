<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Path implements InjectionMatcherInterface
{
    public function __construct(
        public array $scopes,
    ) {}

    public function matches(array $scopes): bool
    {
        dd();
    }
}