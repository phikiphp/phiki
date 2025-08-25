<?php

namespace Phiki\Contracts;

use Phiki\Grammar\ParsedGrammar;

interface RequiresGrammarInterface
{
    /**
     * Set the parsed grammar for the transformer.
     */
    public function withGrammar(ParsedGrammar $grammar): void;
}
