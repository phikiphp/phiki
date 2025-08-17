<?php

use Phiki\Highlighting\Highlighter;
use Phiki\Theme\Theme;

it('can be constructed', function () {
    $theme = Theme::parse([
        'name' => 'test',
        'colors' => [
            'editor.background' => '#000',
            'editor.foreground' => '#fff',
        ],
        'tokenColors' => [],
    ]);

    expect(new Highlighter(['default' => $theme]))->toBeInstanceOf(Highlighter::class);
});
