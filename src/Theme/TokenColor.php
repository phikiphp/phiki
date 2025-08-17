<?php

namespace Phiki\Theme;

use Phiki\Support\Arr;

class TokenColor
{
    /**
     * @param Scope[] $scope
     */
    public function __construct(
        public array $scope,
        public TokenSettings $settings,
    ) {}

    public function match(array $scopes): ScopeMatchResult|false
    {
        foreach ($this->scope as $scope) {
            if ($result = $scope->matches($scopes)) {
                return $result;
            }
        }

        return false;
    }
}
