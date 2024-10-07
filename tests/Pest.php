<?php

use Phiki\Grammar\ParsedGrammar;
use Phiki\Tokenizer;

function tokenize(string $input, array $grammar): array
{
    $tokenizer = new Tokenizer(
        ParsedGrammar::fromArray($grammar)
    );

    return $tokenizer->tokenize($input);
}
