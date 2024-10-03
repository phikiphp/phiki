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

    /**
     * This method will return the same RegEx pattern with any unescaped
     * PHP delimiters escaped.
     * 
     * @param  string  $regex
     * @return string
     */
    protected function escapeDelimiters(string $regex): string
    {
        return preg_replace('/(?<!\\\)\//', '\\/', $regex);
    }
}
