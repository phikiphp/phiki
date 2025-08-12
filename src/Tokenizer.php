<?php

namespace Phiki;

use Phiki\Environment\Environment;
use Phiki\Grammar\Grammar;
use Phiki\Token\Token;

class Tokenizer
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected Grammar $grammar,
        protected Environment $environment,
    ) {}

    /**
     * Tokenize the given text.
     * 
     * @return array<Token[]>
     */
    public function tokenize(string $text): array
    {
        
    }
}
