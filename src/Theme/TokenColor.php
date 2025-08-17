<?php

namespace Phiki\Theme;

class TokenColor
{
    /**
     * @param Scope[] $scope
     */
    public function __construct(
        public array $scope,
        public TokenSettings $settings,
    ) {}
}
