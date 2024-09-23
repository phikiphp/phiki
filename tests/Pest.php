<?php

use Phiki\Tokenizer;

function tokenize(string $input, array $grammar): array
{
    $tokenizer = new Tokenizer($grammar);

    return $tokenizer->tokenize($input);
}
