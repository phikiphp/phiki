<?php

use Phiki\Environment\Environment;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\GrammarRepository;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Highlighting\Highlighter;
use Phiki\Tests\LaravelTestCase;
use Phiki\TextMate\Tokenizer;
use Phiki\Theme\Theme;

pest()->uses(LaravelTestCase::class)->in('Adapters/Laravel');

function tokenize(string $input, array|Grammar $grammar): array
{
    if (! $grammar instanceof Grammar) {
        if (! isset($grammar['scopeName'])) {
            $grammar['scopeName'] = 'source.test';
        }

        $parsedGrammar = ParsedGrammar::fromArray($grammar);
    } else {
        $parsedGrammar = $grammar->toParsedGrammar(new GrammarRepository);
    }

    $tokenizer = new Tokenizer($parsedGrammar, Environment::default());

    return $tokenizer->tokenize($input);
}

function highlight(array $tokens, array $theme): array
{
    if (! isset($theme['default'])) {
        $theme = ['default' => $theme];
    }

    foreach ($theme as &$value) {
        if (! isset($value['name'])) {
            $value['name'] = 'test';
        }

        if (! isset($value['colors'])) {
            $value['colors'] = [
                'editor.background' => '#000',
                'editor.foreground' => '#fff',
            ];
        }

        $value = Theme::parse($value);
    }

    return (new Highlighter($theme))->highlight($tokens);
}
