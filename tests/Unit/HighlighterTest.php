<?php

use Phiki\Highlighter;
use Phiki\Theme\ThemeStyles;

describe('Highlighter', function () {
    it('can be constructed', function () {
        $styles = new ThemeStyles([
            'colors' => [
                'editor.background' => '#000',
                'editor.foreground' => '#fff',
            ],
        ]);

        expect(new Highlighter($styles))->toBeInstanceOf(Highlighter::class);
    });
});
