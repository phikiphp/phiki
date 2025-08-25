<?php

namespace Phiki\Transformers\Concerns;

use Phiki\Grammar\ParsedGrammar;

trait RequiresGrammar
{
    protected ParsedGrammar $grammar;

    public function withGrammar(ParsedGrammar $grammar): void
    {
        $this->grammar = $grammar;
    }
}
