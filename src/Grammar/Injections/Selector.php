<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Selector implements InjectionMatcherInterface
{
    /**
     * @param  array<Composite>  $composites
     */
    public function __construct(
        public array $composites,
    ) {}

    public function getPrefix(array $scopes): ?Prefix
    {
        foreach ($this->composites as $composite) {
            if ($composite->matches($scopes)) {
                return $composite->getPrefix($scopes);
            }
        }

        return null;
    }

    public function matches(array $scopes): bool
    {
        foreach ($this->composites as $composite) {
            if ($composite->matches($scopes)) {
                return true;
            }
        }

        return false;
    }
}
