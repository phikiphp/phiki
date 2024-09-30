<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Selector implements InjectionMatcherInterface
{
    public function __construct(
        public array $composites,
    ) {}

    public function matches(array $scopes): bool
    {
        dd();
    }
}