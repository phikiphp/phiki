<?php

use Phiki\Highlighting\Highlighter;
use Phiki\Theme\ParsedTheme;

it('can be constructed', function () {
    $theme = ParsedTheme::fromArray([
        'name' => 'test',
        'colors' => [
            'editor.background' => '#000',
            'editor.foreground' => '#fff',
        ],
    ]);

    expect(new Highlighter(['default' => $theme]))->toBeInstanceOf(Highlighter::class);
});
