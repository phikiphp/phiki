<?php

namespace Phiki;

class LocalStackElement
{
    public function __construct(
        public AttributedScopeStack $scopes,
        public int $endPos,
    ) {}
}
