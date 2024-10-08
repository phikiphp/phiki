<?php

use Phiki\Phiki;
use Phiki\Token\Token;

describe('php', function () {
    it('correctly tokenizes a double-quoted string', function () {
        $tokens = php('"Hello, world!"');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.string.begin.php'], '"', 0, 1),
                new Token(['source.php', 'string.quoted.double.php'], 'Hello, world!', 1, 14),
                new Token(['source.php', 'string.quoted.double.php', 'punctuation.definition.string.end.php'], '"', 14, 15),
                new Token(['source.php'], "\n", 15, 15),
            ],
        ]);
    });

    it('correctly tokenizes a simple variable', function () {
        $tokens = php('$name');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'variable.other.php', 'punctuation.definition.variable.php'], '$', 0, 1),
                new Token(['source.php', 'variable.other.php'], 'name', 1, 5),
                new Token(['source.php'], "\n", 5, 5),
            ],
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
                new Token(['source.php'], "\n", 17, 17),
            ],
        ]);
    });

    it('correctly tokenizes a class with extends', function () {
        $tokens = php('class A extends B {}');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'meta.class.php', 'storage.type.class.php'], 'class', 0, 5),
                new Token(['source.php', 'meta.class.php'], ' ', 5, 6),
                new Token(['source.php', 'meta.class.php', 'entity.name.type.class.php'], 'A', 6, 7),
                new Token(['source.php', 'meta.class.php'], ' ', 7, 8),
                new Token(['source.php', 'meta.class.php', 'storage.modifier.extends.php'], 'extends', 8, 15),
                new Token(['source.php', 'meta.class.php'], ' ', 15, 16),
                new Token(['source.php', 'meta.class.php', 'entity.other.inherited-class.php'], 'B', 16, 17),
                new Token(['source.php', 'meta.class.php'], ' ', 17, 18),
                new Token(['source.php', 'meta.class.php', 'punctuation.definition.class.begin.bracket.curly.php'], '{', 18, 19),
                new Token(['source.php', 'meta.class.php', 'punctuation.definition.class.end.bracket.curly.php'], '}', 19, 20),
                new Token(['source.php'], "\n", 20, 20),
            ],
        ]);
    });

    it('correctly tokenizes a top-level use statement without namespace separators', function () {
        $tokens = php('use A;');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'meta.use.php', 'keyword.other.use.php'], 'use', 0, 3),
                new Token(['source.php', 'meta.use.php'], ' ', 3, 4),
                new Token(['source.php', 'meta.use.php', 'support.class.php'], 'A', 4, 5),
                new Token(['source.php', 'punctuation.terminator.expression.php'], ';', 5, 6),
                new Token(['source.php'], "\n", 6, 6),
            ],
        ]);
    });

    it('correctly tokenizes a top-level use statement with namespace separators', function () {
        $tokens = php('use A\\B\\C;');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'meta.use.php', 'keyword.other.use.php'], 'use', 0, 3),
                new Token(['source.php', 'meta.use.php'], ' ', 3, 4),
                new Token(['source.php', 'meta.use.php', 'support.other.namespace.php'], 'A', 4, 5),
                new Token(['source.php', 'meta.use.php', 'support.other.namespace.php', 'punctuation.separator.inheritance.php'], '\\', 5, 6),
                new Token(['source.php', 'meta.use.php', 'support.other.namespace.php'], 'B', 6, 7),
                new Token(['source.php', 'meta.use.php', 'support.other.namespace.php', 'punctuation.separator.inheritance.php'], '\\', 7, 8),
                new Token(['source.php', 'meta.use.php', 'support.class.php'], 'C', 8, 9),
                new Token(['source.php', 'punctuation.terminator.expression.php'], ';', 9, 10),
                new Token(['source.php'], "\n", 10, 10),
            ],
        ]);
    });

    it('correctly tokenizes a function statement with a typed parameter', function () {
        $tokens = php('function a(B $b) {}');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'meta.function.php', 'storage.type.function.php'], 'function', 0, 8),
                new Token(['source.php', 'meta.function.php'], ' ', 8, 9),
                new Token(['source.php', 'meta.function.php', 'entity.name.function.php'], 'a', 9, 10),
                new Token(['source.php', 'meta.function.php', 'punctuation.definition.parameters.begin.bracket.round.php'], '(', 10, 11),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'support.class.php'], 'B', 11, 12),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php'], ' ', 12, 13),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'variable.other.php', 'punctuation.definition.variable.php'], '$', 13, 14),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'variable.other.php'], 'b', 14, 15),
                new Token(['source.php', 'meta.function.php', 'punctuation.definition.parameters.end.bracket.round.php'], ')', 15, 16),
                new Token(['source.php'], ' ', 16, 17),
                new Token(['source.php', 'punctuation.definition.begin.bracket.curly.php'], '{', 17, 18),
                new Token(['source.php', 'punctuation.definition.end.bracket.curly.php'], '}', 18, 19),
                new Token(['source.php'], "\n", 19, 19),
            ],
        ]);
    });

    it('correctly tokenizes a function statement with a qualified typed parameter', function () {
        $tokens = php('function a(A\B $b) {}');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'meta.function.php', 'storage.type.function.php'], 'function', 0, 8),
                new Token(['source.php', 'meta.function.php'], ' ', 8, 9),
                new Token(['source.php', 'meta.function.php', 'entity.name.function.php'], 'a', 9, 10),
                new Token(['source.php', 'meta.function.php', 'punctuation.definition.parameters.begin.bracket.round.php'], '(', 10, 11),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'support.other.namespace.php'], 'A', 11, 12),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'support.other.namespace.php', 'punctuation.separator.inheritance.php'], '\\', 12, 13),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'support.class.php'], 'B', 13, 14),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php'], ' ', 14, 15),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'variable.other.php', 'punctuation.definition.variable.php'], '$', 15, 16),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'variable.other.php'], 'b', 16, 17),
                new Token(['source.php', 'meta.function.php', 'punctuation.definition.parameters.end.bracket.round.php'], ')', 17, 18),
                new Token(['source.php'], ' ', 18, 19),
                new Token(['source.php', 'punctuation.definition.begin.bracket.curly.php'], '{', 19, 20),
                new Token(['source.php', 'punctuation.definition.end.bracket.curly.php'], '}', 20, 21),
                new Token(['source.php'], "\n", 21, 21),
            ],
        ]);
    });

    it('correctly tokenizes a function statement with a union type parameter', function () {
        $tokens = php('function a(int|float $b) {}');

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.php', 'meta.function.php', 'storage.type.function.php'], 'function', 0, 8),
                new Token(['source.php', 'meta.function.php'], ' ', 8, 9),
                new Token(['source.php', 'meta.function.php', 'entity.name.function.php'], 'a', 9, 10),
                new Token(['source.php', 'meta.function.php', 'punctuation.definition.parameters.begin.bracket.round.php'], '(', 10, 11),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'keyword.other.type.php'], 'int', 11, 14),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'punctuation.separator.delimiter.php'], '|', 14, 15),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'keyword.other.type.php'], 'float', 15, 20),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php'], ' ', 20, 21),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'variable.other.php', 'punctuation.definition.variable.php'], '$', 21, 22),
                new Token(['source.php', 'meta.function.php', 'meta.function.parameters.php', 'meta.function.parameter.typehinted.php', 'variable.other.php'], 'b', 22, 23),
                new Token(['source.php', 'meta.function.php', 'punctuation.definition.parameters.end.bracket.round.php'], ')', 23, 24),
                new Token(['source.php'], ' ', 24, 25),
                new Token(['source.php', 'punctuation.definition.begin.bracket.curly.php'], '{', 25, 26),
                new Token(['source.php', 'punctuation.definition.end.bracket.curly.php'], '}', 26, 27),
                new Token(['source.php'], "\n", 27, 27),
            ],
        ]);
    });
});

function php(string $input): array
{
    return (new Phiki)->codeToTokens($input, 'php');
}
