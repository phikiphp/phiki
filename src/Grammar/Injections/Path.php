<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Path implements InjectionMatcherInterface
{
    public function __construct(
        public array $scopes,
    ) {}

    public function getPrefix(array $scopes): ?Prefix
    {
        return null;
    }

    public function matches(array $scopes): bool
    {
        $index = 0;
        $current = $this->scopes[$index];

        foreach ($scopes as $scope) {
            $scope = Scope::fromString($scope);

            if ($current->matches($scope)) {
                $current = $this->scopes[++$index] ?? null;
            }

            if ($current === null) {
                return true;
            }
        }

        return false;
    }
}
