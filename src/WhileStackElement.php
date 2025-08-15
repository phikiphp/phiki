<?php

namespace Phiki;

use Phiki\Grammar\WhilePattern;

class WhileStackElement
{
    public function __construct(
        public StateStack $stack,
        public WhilePattern $rule,
    ) {}
}
