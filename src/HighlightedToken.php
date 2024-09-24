<?php

namespace Phiki;

readonly class HighlightedToken
{
    public function __construct(
        public Token $token,
        public ?TokenSettings $settings,
    ) {}
}
