<?php

use Phiki\Grammar\Grammar;
use Phiki\Tokenizer;

function tokenize(string $input, array $grammar): array
{
    $tokenizer = new Tokenizer(
        Grammar::parse($grammar)
    );

    return $tokenizer->tokenize($input);
}
