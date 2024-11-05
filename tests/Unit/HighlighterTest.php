<?php

use Phiki\Highlighter;
use Phiki\Theme\ParsedTheme;

describe('Highlighter', function () {
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
});
