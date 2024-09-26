<?php

use Phiki\Grammar\Grammar;
use Phiki\Tokenizer;

describe('html', function () {
    it('correctly tokenizes a basic tag', function () {
        $tokens = html('<div></div>');

        dd($tokens);
    })->skip();
});

function html(string $input): array
{
    $tokenizer = new Tokenizer(
        Grammar::parse(json_decode(file_get_contents(__DIR__ . '/../../languages/html.json'), true))
    );

    return $tokenizer->tokenize($input);
}