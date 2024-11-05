<?php

namespace Phiki\Token;

use Phiki\Theme\TokenSettings;

readonly class HighlightedToken
{
    /**
     * @param  array<string, TokenSettings>  $settings
     */
    public function __construct(
        public Token $token,
        public array $settings,
    ) {}
}
