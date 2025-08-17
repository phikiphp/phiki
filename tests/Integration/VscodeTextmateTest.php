<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;

$phiki = new Phiki;
it('generates the same tokens as vscode-textmate', function (Grammar $grammar) use ($phiki) {
    $sample = file_get_contents(__DIR__ . "/../Fixtures/samples/{$grammar->value}.sample");
    // $tokens = (new Phiki)->codeToTokens($sample, $grammar); 
    // $vscodeTokens = json_decode(file_get_contents(__DIR__ . "/../Fixtures/vscode-tokens/{$grammar->value}.json"), true);
})
    ->with(array_filter(Grammar::cases(), fn (Grammar $grammar) => file_exists(__DIR__ . "/../Fixtures/samples/{$grammar->value}.sample")));
