<?php

namespace Phiki\Contracts;

interface InjectionMatcherInterface
{
    /**
     * Determine whether this node matches the given list of scopes.
     *
     * @param  string[]  $scopes
     */
    public function matches(array $scopes): bool;
}
