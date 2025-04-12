<?php

use Phiki\Phiki;

pest()->group('integration/grammars');

describe('Grammars', function () {
    test('default grammars do not produce warnings or exceptions', function (string $grammar) {
        $sample = file_get_contents(__DIR__.'/../../resources/samples/'.$grammar.'.sample');

        (new Phiki)->codeToTokens($sample, $grammar);
    })
        ->with('grammars')
        ->throwsNoExceptions();
});
