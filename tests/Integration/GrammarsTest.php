<?php

use Phiki\Grammar\GrammarRepository;
use Phiki\Phiki;

describe('Grammars', function () {
    test('default grammars do not produce warnings or exceptions', function (string $grammar) {
        $sample = file_get_contents(__DIR__.'/../../resources/samples/'.$grammar.'.sample');

        (new Phiki)->codeToTokens($sample, $grammar);
    })
        ->with('grammars')
        ->throwsNoExceptions();
});

dataset('grammars', function () {
    $repository = new GrammarRepository;
    $grammars = array_filter($repository->getAllGrammarNames(), fn (string $grammar) => ! in_array($grammar, [
        'astro',
        'haxe',
        'fluent',
        'stylus',
        'svelte',
        'viml',
        'sas',
        'git-commit',
        'hxml',
        'groovy',
        'make',
        'shellsession',
        // Act as includes, basically.
        'html-derivative',
        'cpp-macro',
        'jinja-html',
        // No sample file.
        'git-rebase',
        // Empty.
        'txt',
    ]));

    sort($grammars, SORT_NATURAL);

    // FIXME: These grammars have known issues and should be skipped.
    return array_values($grammars);
});
