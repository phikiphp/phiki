<?php

namespace Phiki;

class LocalStackElement
{
    public function __construct(
        public readonly AttributedScopeStack $scopes,
        public readonly int $endPos,
    ) {}
}
