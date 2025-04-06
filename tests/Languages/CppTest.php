<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Token\Token;

it('does not produce duplicated text for small reproduction', function () {
    $text = <<<'CPP'
    return 0; /* comment */
    CPP;

    $output = (new Phiki)->codeToTokens($text, Grammar::Cpp);

    expect($output)
        ->toEqualCanonicalizing([
            [
                new Token(['source.cpp', 'keyword.control.return.cpp'], 'return', 0, 6),
                new Token(['source.cpp'], ' ', 6, 7),
                new Token(['source.cpp'], '0', 7, 8),
                new Token(['source.cpp', 'punctuation.terminator.statement.cpp'], ';', 8, 9),
                new Token(['source.cpp', 'comment.block.cpp'], ' ', 9, 10),
                new Token(['source.cpp', 'comment.block.cpp', 'punctuation.definition.comment.begin.cpp'], '/*', 10, 12),
                new Token(['source.cpp', 'comment.block.cpp'], ' comment ', 12, 21),
                new Token(['source.cpp', 'comment.block.cpp', 'punctuation.definition.comment.end.cpp'], '*/', 21, 23),
                new Token(['source.cpp'], "\n", 23, 23),
            ],
        ]);
})->issue(57);
