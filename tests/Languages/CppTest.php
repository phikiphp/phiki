<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;

it('does not produce duplicated text', function () {
    $text = <<<'CPP'
    return 0; /* comment */
    CPP;

    $output = (new Phiki)->codeToTokens($text, Grammar::Cpp);

    dd($output);
})->only();
