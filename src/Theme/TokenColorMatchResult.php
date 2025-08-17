<?php

namespace Phiki\Theme;

class TokenColorMatchResult
{
    public function __construct(
        public TokenColor $tokenColor,
        public ScopeMatchResult $scopeMatchResult,
    ) {}
}
