<?php

namespace Phiki\Theme;

class ScopeMatchResult
{
    public function __construct(
        public int $length,
        public int $depth,
        public int $ancestral = 0,
    ) {}
}
