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
