<?php

use Phiki\Environment\Environment;
use Phiki\Grammar\DefaultGrammars;
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
            $grammarName,
            json_encode(collect(DefaultGrammars::SCOPES_TO_NAMES)
                ->mapWithKeys(fn (string $name, string $scope) => [$scope => DefaultGrammars::NAMES_TO_PATHS[$name]])
                ->all()),
        ],
    );

    $process->run();

    if (! $process->isSuccessful()) {
        throw new RuntimeException($process->getErrorOutput());
    }

    $output = json_decode($process->getOutput(), true);

    if (! is_array($output)) {
        throw new RuntimeException('Invalid output from process');
    }

    return collect($output)
        ->map(fn (array $token) => new Token(
            scopes: $token['scopes'],
            text: $token['text'],
            start: $token['start'],
            end: $token['end'],
        ))
        ->all();
}
