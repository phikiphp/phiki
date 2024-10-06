<?php

namespace Phiki\Contracts;

use Phiki\Grammar\Injections\Prefix;

interface InjectionMatcherInterface
{
    /**
     * Determine whether this node matches the given list of scopes.
     *
     * @param  string[]  $scopes
     */
    public function matches(array $scopes): bool;

    /**
     * Get the prefix position for the node.
     *
     * @param  string[]  $scopes
     */
    public function getPrefix(array $scopes): ?Prefix;
}
