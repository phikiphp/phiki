<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;

it('generates the same tokens as vscode-textmate', function (Grammar $grammar) {
    $sample = file_get_contents(__DIR__ . "/../Fixtures/samples/{$grammar->value}.sample");
    // $tokens = (new Phiki)->codeToTokens($sample, $grammar);
    $vscodeTokens = json_decode(shell_exec('node ' . realpath(__DIR__ . '/../../meta/generate-vscode-textmate-tokens.js') . ' ' . escapeshellarg($sample) . ' ' . escapeshellarg($grammar->scopeName())), true);
    
    foreach ($vscodeTokens as $index => $vscodeLineTokens) {
        
    }
})
    ->with(array_filter(Grammar::cases(), fn (Grammar $grammar) => file_exists(__DIR__ . "/../Fixtures/samples/{$grammar->value}.sample")));
