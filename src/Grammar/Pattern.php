<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternInterface;

abstract class Pattern implements PatternInterface
{
    public function produceScopes(array $scopes): array
    {
        $scope = $this->scope();

        if ($scope === null) {
            return $scopes;
        }

        return array_merge($scopes, [$scope]);
    }
}