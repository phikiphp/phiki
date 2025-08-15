<?php

use Phiki\Token\Token;

describe('match', function () {
    it('can tokenize simple match patterns', function () {
        $tokens = tokenize('if else while end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'keyword.control.test',
                    'match' => '\\b(if|else|while|end)\\b',
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'keyword.control.test'], 'if', 0, 2),
                new Token(['source.test'], ' ', 2, 3),
                new Token(['source.test', 'keyword.control.test'], 'else', 3, 7),
                new Token(['source.test'], ' ', 7, 8),
                new Token(['source.test', 'keyword.control.test'], 'while', 8, 13),
                new Token(['source.test'], ' ', 13, 14),
                new Token(['source.test', 'keyword.control.test'], 'end', 14, 17),
                new Token(['source.test'], "\n", 17, 18),
            ],
        ]);
    });

    it('can tokenize a simple match with simple named captures', function () {
        $tokens = tokenize('function foo() {}', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.function.test',
                    'match' => '(function)\\s*([a-zA-Z_\\x{7f}-\\x{10ffff}][a-zA-Z0-9_\\x{7f}-\\x{10ffff}]*)',
                    'captures' => [
                        '1' => [
                            'name' => 'storage.type.function.test',
                        ],
                        '2' => [
                            'name' => 'entity.name.function.test',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.function.test', 'storage.type.function.test'], 'function', 0, 8),
                new Token(['source.test', 'meta.function.test'], ' ', 8, 9),
                new Token(['source.test', 'meta.function.test', 'entity.name.function.test'], 'foo', 9, 12),
                new Token(['source.test'], "() {}\n", 12, 18),
            ],
        ]);
    });

    it('can tokenize a match with captures and subpatterns, where the subpatterns are not found', function () {
        $tokens = tokenize('namespace Foo;', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.namespace.test',
                    'match' => '(?i)(?:^|(?<=<\\?php))\\s*(namespace)\\s+([a-z0-9_\\x{7f}-\\x{10ffff}\\\\]+)(?=\\s*;)',
                    'captures' => [
                        '1' => [
                            'name' => 'keyword.other.namespace.test',
                        ],
                        '2' => [
                            'name' => 'entity.name.type.namespace.test',
                            'patterns' => [
                                [
                                    'match' => '\\\\',
                                    'name' => 'punctuation.separator.inheritance.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.namespace.test', 'keyword.other.namespace.test'], 'namespace', 0, 9),
                new Token(['source.test', 'meta.namespace.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Foo', 10, 13),
                new Token(['source.test'], ";\n", 13, 15),
            ],
        ]);
    });

    it('can tokenize a match with captures and subpatterns, where the subpatterns are found', function () {
        $tokens = tokenize('namespace Foo\\Bar\\Baz;', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.namespace.test',
                    'match' => '(?i)(?:^|(?<=<\\?php))\\s*(namespace)\\s+([a-z0-9_\\x{7f}-\\x{10ffff}\\\\]+)(?=\\s*;)',
                    'captures' => [
                        '1' => [
                            'name' => 'keyword.other.namespace.test',
                        ],
                        '2' => [
                            'name' => 'entity.name.type.namespace.test',
                            'patterns' => [
                                [
                                    'match' => '\\\\',
                                    'name' => 'punctuation.separator.inheritance.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.namespace.test', 'keyword.other.namespace.test'], 'namespace', 0, 9),
                new Token(['source.test', 'meta.namespace.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Foo', 10, 13),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test', 'punctuation.separator.inheritance.test'], '\\', 13, 14),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Bar', 14, 17),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test', 'punctuation.separator.inheritance.test'], '\\', 17, 18),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Baz', 18, 21),
                new Token(['source.test'], ";\n", 21, 23),
            ],
        ]);
    });
});

describe('begin/end', function () {
    it('can tokenize a simple begin/end pattern without captures and subpatterns', function () {
        $tokens = tokenize('begin end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test'], 'end', 6, 9),
                new Token(['source.test'], "\n", 9, 10),
            ],
        ]);
    });

    it('can tokenize a simple begin/end pattern with beginCaptures and endCaptures', function () {
        $tokens = tokenize('begin end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'beginCaptures' => [
                        '1' => [
                            'name' => 'keyword.control.test',
                        ],
                    ],
                    'end' => '\\b(end)\\b',
                    'endCaptures' => [
                        '1' => [
                            'name' => 'keyword.control.test',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test', 'keyword.control.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'keyword.control.test'], 'end', 6, 9),
                new Token(['source.test'], "\n", 9, 10),
            ],
        ]);
    });

    it('can tokenize a simple begin/end patterns with captures', function () {
        $tokens = tokenize('begin end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'captures' => [
                        '1' => [
                            'name' => 'keyword.control.test',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test', 'keyword.control.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'keyword.control.test'], 'end', 6, 9),
                new Token(['source.test'], "\n", 9, 10),
            ],
        ]);
    });

    it('can tokenize a simple begin/end pattern with beginCaptures that have subpatterns', function () {
        $tokens = tokenize('begin end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'beginCaptures' => [
                        '1' => [
                            'name' => 'keyword.control.test',
                            'patterns' => [
                                [
                                    'match' => 'begin',
                                    'name' => 'keyword.control.begin.test',
                                ],
                            ],
                        ],
                    ],
                    'end' => '\\b(end)\\b',
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test', 'keyword.control.test', 'keyword.control.begin.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test'], 'end', 6, 9),
                new Token(['source.test'], "\n", 9, 10),
            ],
        ]);
    });

    it('can tokenize a simple begin/end pattern with endCaptures that have subpatterns', function () {
        $tokens = tokenize('begin end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'endCaptures' => [
                        '1' => [
                            'name' => 'keyword.control.test',
                            'patterns' => [
                                [
                                    'match' => 'end',
                                    'name' => 'keyword.control.end.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'keyword.control.test', 'keyword.control.end.test'], 'end', 6, 9),
                new Token(['source.test'], "\n", 9, 10),
            ],
        ]);
    });

    it('can tokenize a simple begin/end pattern that has captures with subpatterns', function () {
        $tokens = tokenize('begin end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'captures' => [
                        '1' => [
                            'name' => 'keyword.control.test',
                            'patterns' => [
                                [
                                    'match' => 'begin',
                                    'name' => 'keyword.control.begin.test',
                                ],
                                [
                                    'match' => 'end',
                                    'name' => 'keyword.control.end.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test', 'keyword.control.test', 'keyword.control.begin.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'keyword.control.test', 'keyword.control.end.test'], 'end', 6, 9),
                new Token(['source.test'], "\n", 9, 10),
            ],
        ]);
    });

    it('can tokenize a begin/end pattern with subpatterns between begin and end', function () {
        $tokens = tokenize('begin foo end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'patterns' => [
                        [
                            'name' => 'entity.name.test',
                            'match' => '\\b(foo)\\b',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'entity.name.test'], 'foo', 6, 9),
                new Token(['source.test', 'meta.block.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.block.test'], 'end', 10, 13),
                new Token(['source.test'], "\n", 13, 14),
            ],
        ]);
    });

    it('can tokenize a begin/end pattern with subpatterns between begin and end that have captures', function () {
        $tokens = tokenize('begin foo end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'patterns' => [
                        [
                            'name' => 'entity.name.test',
                            'match' => '\\b(foo)\\b',
                            'captures' => [
                                '1' => [
                                    'name' => 'entity.name.foo.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'entity.name.test', 'entity.name.foo.test'], 'foo', 6, 9),
                new Token(['source.test', 'meta.block.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.block.test'], 'end', 10, 13),
                new Token(['source.test'], "\n", 13, 14),
            ],
        ]);
    });

    it('can tokenize a begin/end patterns that have subpatterns and span multiple lines', function () {
        $tokens = tokenize(<<<'TEST'
        begin
            foo
        end
        TEST, [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'patterns' => [
                        [
                            'name' => 'entity.name.test',
                            'match' => '\\b(foo)\\b',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], "\n", 5, 6),
            ],
            [
                new Token(['source.test', 'meta.block.test'], '    ', 0, 4),
                new Token(['source.test', 'meta.block.test', 'entity.name.test'], 'foo', 4, 7),
                new Token(['source.test', 'meta.block.test'], "\n", 7, 8),
            ],
            [
                new Token(['source.test', 'meta.block.test'], 'end', 0, 3),
                new Token(['source.test'], "\n", 3, 4),
            ],
        ]);
    });

    it('can tokenize a begin/end patterns that have captures with subpatterns that have captures', function () {
        $tokens = tokenize(<<<'TEST'
        begin
            foo
        end
        TEST, [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'captures' => [
                        '1' => [
                            'name' => 'keyword.control.test',
                            'patterns' => [
                                [
                                    'match' => 'begin',
                                    'name' => 'keyword.control.begin.test',
                                ],
                                [
                                    'match' => 'end',
                                    'name' => 'keyword.control.end.test',
                                ],
                            ],
                        ],
                    ],
                    'patterns' => [
                        [
                            'name' => 'entity.name.test',
                            'match' => '\\b(foo)\\b',
                            'captures' => [
                                '1' => [
                                    'name' => 'entity.name.foo.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test', 'keyword.control.test', 'keyword.control.begin.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], "\n", 5, 6),
            ],
            [
                new Token(['source.test', 'meta.block.test'], '    ', 0, 4),
                new Token(['source.test', 'meta.block.test', 'entity.name.test', 'entity.name.foo.test'], 'foo', 4, 7),
                new Token(['source.test', 'meta.block.test'], "\n", 7, 8),
            ],
            [
                new Token(['source.test', 'meta.block.test', 'keyword.control.test', 'keyword.control.end.test'], 'end', 0, 3),
                new Token(['source.test'], "\n", 3, 4),
            ],
        ]);
    });

    it('adds contentName to the scope stack when processing begin/end patterns', function () {
        $tokens = tokenize('begin foo end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'contentName' => 'meta.begin.end.block.test',
                    'patterns' => [
                        [
                            'name' => 'entity.name.test',
                            'match' => '\\b(foo)\\b',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test', 'meta.begin.end.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'meta.begin.end.block.test', 'entity.name.test'], 'foo', 6, 9),
                new Token(['source.test', 'meta.block.test', 'meta.begin.end.block.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.block.test'], 'end', 10, 13),
                new Token(['source.test'], "\n", 13, 14),
            ],
        ]);
    });

    it('can tokenize a begin/end pattern where the end pattern matches before a subpattern', function () {
        $tokens = tokenize('begin end foo', [
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'patterns' => [
                        [
                            'name' => 'entity.name.test',
                            'match' => '\\b(foo)\\b',
                        ],
                    ],
                ]
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test'], 'end', 6, 9),
                new Token(['source.test'], " foo\n", 9, 14),
            ]
        ]);
    });

    it('can tokenize a begin/end pattern where the end pattern matches immediately after a subpattern', function () {
        $tokens = tokenize('begin foo end foo', [
            'patterns' => [
                [
                    'name' => 'meta.block.test',
                    'begin' => '\\b(begin)\\b',
                    'end' => '\\b(end)\\b',
                    'patterns' => [
                        [
                            'name' => 'entity.name.test',
                            'match' => '\\b(foo)\\b',
                        ],
                    ],
                ]
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.block.test'], 'begin', 0, 5),
                new Token(['source.test', 'meta.block.test'], ' ', 5, 6),
                new Token(['source.test', 'meta.block.test', 'entity.name.test'], 'foo', 6, 9),
                new Token(['source.test', 'meta.block.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.block.test'], 'end', 10, 13),
                new Token(['source.test'], " foo\n", 13, 18),
            ]
        ]);
    });
});

describe('scopes', function () {
    it('correctly replaces capture references inside of scope names', function () {
        $tokens = tokenize('foo', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'entity.name.test',
                    'match' => '\\b(foo)\\b',
                    'captures' => [
                        '1' => [
                            'name' => 'entity.name.$1.test',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'entity.name.test', 'entity.name.foo.test'], 'foo', 0, 3),
                new Token(['source.test'], "\n", 3, 4),
            ],
        ]);
    });
});

describe('while', function () {
    it('correctly processes while patterns', function () {
        $tokens = tokenize(
            <<<'MARKDOWN'
            > first line
            > second line
            > third line
            MARKDOWN,
            [
                'patterns' => [
                    [
                        'begin' => '(^|\\G)[ ]{0,3}(>) ?',
                        'captures' => [
                            '2' => [
                                'name' => 'punctuation.definition.quote.begin.test',
                            ],
                        ],
                        'name' => 'markup.quote.block.test',
                        'patterns' => [
                            [
                                'match' => '.*',
                                'name' => 'markup.quote.block.line.test',
                            ]
                        ],
                        'while' => '(^|\\G)\\s*(>) ?',
                    ]
                ],
            ]
        );

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'markup.quote.block.test', 'punctuation.definition.quote.begin.test'], '>', 0, 1),
                new Token(['source.test', 'markup.quote.block.test'], ' ', 1, 2),
                new Token(['source.test', 'markup.quote.block.test', 'markup.quote.block.line.test'], "first line\n", 2, 13),
            ],
            [
                new Token(['source.test', 'markup.quote.block.test', 'punctuation.definition.quote.begin.test'], '>', 0, 1),
                new Token(['source.test', 'markup.quote.block.test'], ' ', 1, 2),
                new Token(['source.test', 'markup.quote.block.test', 'markup.quote.block.line.test'], "second line\n", 2, 14),
            ],
            [
                new Token(['source.test', 'markup.quote.block.test', 'punctuation.definition.quote.begin.test'], '>', 0, 1),
                new Token(['source.test', 'markup.quote.block.test'], ' ', 1, 2),
                new Token(['source.test', 'markup.quote.block.test', 'markup.quote.block.line.test'], "third line\n", 2, 13),
            ]
        ]);
    });
});
