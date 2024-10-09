<?php

use Phiki\Phiki;
use Phiki\Token\Token;

describe('javascript', function () {
    it('correctly tokenizes a single-line comment', function () {
        $tokens = js('// This is a comment.');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.js', 'comment.line.double-slash.js', 'punctuation.definition.comment.js'], '//', 0, 2),
                new Token(['source.js', 'comment.line.double-slash.js'], ' This is a comment.', 2, 21),
                new Token(['source.js'], "\n", 21, 21),
            ],
        ]);
    });
});

function js(string $input): array
{
    return (new Phiki)->codeToTokens($input, 'javascript');
}
