<?php

use Phiki\Environment\Environment;
use Phiki\Grammar\ParsedGrammar;
use Phiki\TextMate\Tokenizer;

function tokenize(string $input, array $grammar): array
{
    if (! isset($grammar['scopeName'])) {
        $grammar['scopeName'] = 'source.test';
    }

    $tokenizer = new Tokenizer(
        ParsedGrammar::fromArray($grammar),
        Environment::default()
    );

    return $tokenizer->tokenize($input);
}
