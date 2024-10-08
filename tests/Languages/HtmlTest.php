<?php

use Phiki\Phiki;
use Phiki\Token\Token;

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
                new Token(['text.html.basic'], "\n", 11, 11),
            ],
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
                new Token(['text.html.basic'], "\n", 22, 22),
            ],
        ]);
    });

    it('correctly tokenizes a tag with valueless attributes', function () {
        $tokens = html('<h1 hidden></h1>');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'punctuation.definition.tag.begin.html'], '<', 0, 1),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'entity.name.tag.html'], 'h1', 1, 3),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html'], ' ', 3, 4),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'meta.attribute.hidden.html', 'entity.other.attribute-name.html'], 'hidden', 4, 10),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'punctuation.definition.tag.end.html'], '>', 10, 11),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'punctuation.definition.tag.begin.html'], '</', 11, 13),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'entity.name.tag.html'], 'h1', 13, 15),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'punctuation.definition.tag.end.html'], '>', 15, 16),
                new Token(['text.html.basic'], "\n", 16, 16),
            ],
        ]);
    });

    it('correctly tokenizes a tag with valued attributes', function () {
        $tokens = html('<h1 class="foo"></h1>');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'punctuation.definition.tag.begin.html'], '<', 0, 1),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'entity.name.tag.html'], 'h1', 1, 3),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html'], ' ', 3, 4),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'meta.attribute.class.html', 'entity.other.attribute-name.html'], 'class', 4, 9),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'meta.attribute.class.html', 'punctuation.separator.key-value.html'], '=', 9, 10),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'meta.attribute.class.html', 'string.quoted.double.html', 'punctuation.definition.string.begin.html'], '"', 10, 11),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'meta.attribute.class.html', 'string.quoted.double.html'], 'foo', 11, 14),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'meta.attribute.class.html', 'string.quoted.double.html', 'punctuation.definition.string.end.html'], '"', 14, 15),
                new Token(['text.html.basic', 'meta.tag.structure.h1.start.html', 'punctuation.definition.tag.end.html'], '>', 15, 16),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'punctuation.definition.tag.begin.html'], '</', 16, 18),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'entity.name.tag.html'], 'h1', 18, 20),
                new Token(['text.html.basic', 'meta.tag.structure.h1.end.html', 'punctuation.definition.tag.end.html'], '>', 20, 21),
                new Token(['text.html.basic'], "\n", 21, 21),
            ],
        ]);
    });
});

function html(string $input): array
{
    return (new Phiki)->codeToTokens($input, 'html');
}
