<?php

use Phiki\Theme\ParsedTheme;
use Phiki\Theme\ThemeStyles;
use Phiki\Theme\TokenSettings;

describe('ThemeStyles', function () {
    it('can be constructed', function () {
        $styles = new ThemeStyles(ParsedTheme::fromArray([
            'name' => 'test',
            'colors' => [
                'editor.background' => '#000',
                'editor.foreground' => '#fff',
            ],
        ]));

        expect($styles)->toBeInstanceOf(ThemeStyles::class);
    });

    it('can resolve style settings for the given scope', function () {
        $styles = new ThemeStyles(ParsedTheme::fromArray([
            'name' => 'test',
            'colors' => [
                'editor.background' => '#000',
                'editor.foreground' => '#fff',
            ],
            'tokenColors' => [
                [
                    'scope' => 'comment',
                    'settings' => [
                        'fontStyle' => 'italic',
                        'foreground' => '#888',
                    ],
                ],
                [
                    'scope' => [
                        'keyword',
                    ],
                    'settings' => [
                        'foreground' => '#f97583',
                    ],
                ],
            ],
        ]));

        $settings = $styles->resolve('comment');

        expect($settings)->toEqualCanonicalizing(new TokenSettings(
            null,
            '#888',
            'italic'
        ));

        $settings = $styles->resolve('keyword.function.test');

        expect($settings)->toEqualCanonicalizing(new TokenSettings(
            null,
            '#f97583',
            null
        ));
    });
});
