<?php

use Phiki\Environment\Environment;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Tokenizer;

function tokenize(string $input, array $grammar): array
{
    $tokenizer = new Tokenizer(
        ParsedGrammar::fromArray($grammar),
        Environment::default()
    );

    return $tokenizer->tokenize($input);
}
