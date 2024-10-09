<?php

use Phiki\Phiki;
use Phiki\Token\Token;

describe('yaml', function () {
    it('correctly tokenizes a simple property', function () {
        $tokens = yaml('name: "Hello, world"');

        // NOTE: The YAML grammar is poorly written, hence the weird token output.
        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.yaml', 'string.unquoted.plain.out.yaml', 'entity.name.tag.yaml'], 'n', 0, 1),
                new Token(['source.yaml', 'string.unquoted.plain.out.yaml', 'entity.name.tag.yaml'], 'ame', 1, 4),
                new Token(['source.yaml', 'punctuation.separator.key-value.mapping.yaml'], ':', 4, 5),
                new Token(['source.yaml'], ' ', 5, 6),
                new Token(['source.yaml', 'string.quoted.double.yaml', 'punctuation.definition.string.begin.yaml'], '"', 6, 7),
                new Token(['source.yaml', 'string.quoted.double.yaml'], 'Hello, world', 7, 19),
                new Token(['source.yaml', 'string.quoted.double.yaml', 'punctuation.definition.string.end.yaml'], '"', 19, 20),
                new Token(['source.yaml'], "\n", 20, 20),
            ],
        ]);
    });
});

function yaml(string $input): array
{
    return (new Phiki)->codeToTokens($input, 'yaml');
}
