<?php

namespace Phiki\TextMate;

class LocalStackElement
{
    public function __construct(
        public AttributedScopeStack $scopes,
        public int $endPos,
    ) {}
}
