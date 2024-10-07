<?php

use Phiki\Highlighter;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\ThemeStyles;

describe('Highlighter', function () {
    it('can be constructed', function () {
        $theme = ParsedTheme::fromArray([
            'name' => 'test',
            'colors' => [
                'editor.background' => '#000',
                'editor.foreground' => '#fff',
            ],
        ]);

        expect(new Highlighter($theme))->toBeInstanceOf(Highlighter::class);
    });
});
