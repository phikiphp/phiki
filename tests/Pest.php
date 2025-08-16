<?php

use Phiki\Environment\Environment;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Highlighting\Highlighter;
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

function highlight(array $tokens, array $theme): array
{
    if (! isset($theme['default'])) {
        $theme = ['default' => $theme];
    }

    return (new Highlighter($theme))->highlight($tokens);
}
