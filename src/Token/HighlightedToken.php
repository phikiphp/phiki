<?php

namespace Phiki\Token;

use Phiki\Theme\TokenSettings;

readonly class HighlightedToken
{
    public function __construct(
        public Token $token,
        public ?TokenSettings $settings,
    ) {}
}
