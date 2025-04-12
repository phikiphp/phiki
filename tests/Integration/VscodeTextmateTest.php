<?php

use Phiki\Phiki;

pest()->group('integration/vscode-textmate');

test('it produces the same tokens as vscode-textmate', function (string $grammar) {
    $samplePath = __DIR__ . "/../../resources/samples/{$grammar}.sample";

    $expected = vscodeTextmateTokenize($samplePath, $grammar);
    $actual = (new Phiki)->codeToTokens(file_get_contents($samplePath), $grammar);

    expect($actual)->toEqualCanonicalizing($expected, 'Phiki produced different tokens than vscode-textmate.');
})
    ->with('grammars');
