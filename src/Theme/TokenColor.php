<?php

namespace Phiki\Theme;

class TokenColor
{
    public function __construct(
        public array $scopes,
        public TokenSettings $settings,
    ) {}
}
