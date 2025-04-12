<?php

use Phiki\Environment\Environment;
use Phiki\Grammar\DefaultGrammars;
use Phiki\Grammar\GrammarRepository;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Token\Token;
use Phiki\Tokenizer;
use Symfony\Component\Process\Process;

function tokenize(string $input, array $grammar): array
{
    $tokenizer = new Tokenizer(
        ParsedGrammar::fromArray($grammar),
        Environment::default()
    );

    return $tokenizer->tokenize($input);
}

function vscodeTextmateTokenize(string $samplePath, string $grammarName): array
{
    $process = new Process(
        [
            'node',
            __DIR__ . '/Fixtures/vscode-textmate-compliance.js',
            $samplePath,
            array_flip(DefaultGrammars::SCOPES_TO_NAMES)[$grammarName],
            json_encode(collect(DefaultGrammars::SCOPES_TO_NAMES)
                ->mapWithKeys(fn (string $name, string $scope) => [$scope => DefaultGrammars::NAMES_TO_PATHS[$name]])
                ->all()),
        ],
    );

    $process->run();

    if (! $process->isSuccessful()) {
        throw new RuntimeException($process->getErrorOutput() . ':' . PHP_EOL . $process->getOutput());
    }

    $output = json_decode($process->getOutput(), true);

    if (! is_array($output)) {
        throw new RuntimeException('Invalid output from process:' . PHP_EOL . $process->getOutput());
    }
    
    return array_map(
        fn (array $lineTokens) => array_map(
            fn (array $token) => new Token(
                scopes: $token['scopes'],
                text: $token['text'],
                start: $token['start'],
                end: $token['end'],
            ),
            $lineTokens
        ),
        $output
    );
}

dataset('grammars', function () {
    $repository = new GrammarRepository;
    $grammars = array_filter($repository->getAllGrammarNames(), fn(string $grammar) => ! in_array($grammar, [
        'astro',
        'haxe',
        'fluent',
        'stylus',
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
