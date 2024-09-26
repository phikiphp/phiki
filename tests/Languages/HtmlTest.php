<?php

use Phiki\Grammar\Grammar;
use Phiki\Token;
use Phiki\Tokenizer;

describe('html', function () {
    it('correctly tokenizes a basic tag', function () {
        $tokens = html('<div></div>');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['text.html.basic', 'meta.tag.structure.div.start.html', 'punctuation.definition.tag.begin.html'], '<', 0, 1),
                new Token(['text.html.basic', 'meta.tag.structure.div.start.html', 'entity.name.tag.html'], 'div', 1, 4),
                new Token(['text.html.basic', 'meta.tag.structure.div.start.html', 'punctuation.definition.tag.end.html'], '>', 4, 5),
                new Token(['text.html.basic', 'meta.tag.structure.div.end.html', 'punctuation.definition.tag.begin.html'], '</', 5, 7),
                new Token(['text.html.basic', 'meta.tag.structure.div.end.html', 'entity.name.tag.html'], 'div', 7, 10),
                new Token(['text.html.basic', 'meta.tag.structure.div.end.html', 'punctuation.definition.tag.end.html'], '>', 10, 11),
                new Token(['text.html.basic'], "\n", 11, 11)
            ]
        ]);
    });

    it('correctly tokenizes a basic tag with text in between', function () {
        $tokens = html('<h1>Hello, world!</h1>');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'punctuation.definition.tag.begin.html'], '<', 0, 1),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'entity.name.tag.html'], 'h1', 1, 3),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'punctuation.definition.tag.end.html'], '>', 3, 4),
                new Token(['text.html.basic'], 'Hello, world!', 4, 17),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'punctuation.definition.tag.begin.html'], '</', 17, 19),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'entity.name.tag.html'], 'h1', 19, 21),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'punctuation.definition.tag.end.html'], '>', 21, 22),
                new Token(['text.html.basic'], "\n", 22, 22)
            ]
        ]);
    });
});

function html(string $input): array
{
    $tokenizer = new Tokenizer(
        Grammar::parse(json_decode(file_get_contents(__DIR__ . '/../../languages/html.json'), true))
    );

    return $tokenizer->tokenize($input);
}