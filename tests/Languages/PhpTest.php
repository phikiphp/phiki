<?php

use Phiki\Token;
use Phiki\Tokenizer;

describe('php', function () {
    it('correctly tokenizes a double-quoted string', function () {
        $tokens = php('"Hello, world!"');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.string.begin.php'], '"', 0, 1),
                new Token(['source.php', 'string.quoted.double.php'], 'Hello, world!', 1, 14),
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.string.end.php'], '"', 14, 15),
                new Token(['source.php'], "\n", 15, 15)
            ]
        ]);
    });

    it('correctly tokenizes a simple variable', function () {
        $tokens = php('$name');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'variable.other.php', 'punctuation.definition.variable.php'], '$', 0, 1),
                new Token(['source.php', 'variable.other.php'], 'name', 1, 5),
                new Token(['source.php'], "\n", 5, 5)
            ]
        ]);
    });

    it('correctly tokenizes a double-quoted string with interpolation', function () {
        $tokens = php('"Hello, {$name}!"');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.string.begin.php'], '"', 0, 1),
                new Token(['source.php', 'string.quoted.double.php'], 'Hello, ', 1, 8),
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.variable.php'], '{', 8, 9),
                new Token(['source.php', 'string.quoted.double.php', 'variable.other.php', 'punctuation.definition.variable.php'], '$', 9, 10),
                new Token(['source.php', 'string.quoted.double.php', 'variable.other.php'], 'name', 10, 14),
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.variable.php'], '}', 14, 15),
                new Token(['source.php', 'string.quoted.double.php'], '!', 15, 16),
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.string.end.php'], '"', 16, 17),
                new Token(['source.php'], "\n", 17, 17)
            ]
        ]);
    });
});

function php(string $input): array
{
    $tokenizer = new Tokenizer(
        json_decode(file_get_contents(__DIR__ . '/../../languages/php.json'), true)
    );

    return $tokenizer->tokenize($input);
}