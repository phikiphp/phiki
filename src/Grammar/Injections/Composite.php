<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Composite implements InjectionMatcherInterface
{
    public function __construct(
        public array $expressions,
    ) {}

    public function matches(array $scopes): bool
    {
        dd();
    }
}