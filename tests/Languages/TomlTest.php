<?php

use Phiki\Phiki;
use Phiki\Token\Token;

describe('toml', function () {
    it('correctly tokenizes group headers', function () {
        $tokens = toml('[group]');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.toml', 'meta.group.toml', 'punctuation.definition.section.begin.toml'], '[', 0, 1),
                new Token(['source.toml', 'meta.group.toml', 'entity.name.section.toml'], 'group', 1, 6),
                new Token(['source.toml', 'meta.group.toml', 'punctuation.definition.section.begin.toml'], ']', 6, 7),
                new Token(['source.toml'], "\n", 7, 7),
            ],
        ]);
    });

    it('correctly tokenizes group headers with dot-notated names', function () {
        $tokens = toml('[group.subgroup]');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.toml', 'meta.group.toml', 'punctuation.definition.section.begin.toml'], '[', 0, 1),
                new Token(['source.toml', 'meta.group.toml', 'entity.name.section.toml'], 'group', 1, 6),
                new Token(['source.toml', 'meta.group.toml'], '.', 6, 7),
                new Token(['source.toml', 'meta.group.toml', 'entity.name.section.toml'], 'subgroup', 7, 15),
                new Token(['source.toml', 'meta.group.toml', 'punctuation.definition.section.begin.toml'], ']', 15, 16),
                new Token(['source.toml'], "\n", 16, 16),
            ],
        ]);
    });
});

function toml(string $input): array
{
    return (new Phiki)->codeToTokens($input, 'toml');
}
