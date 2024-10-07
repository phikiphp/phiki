<?php

use Phiki\Theme\TokenSettings;

describe('TokenSettings', function () {
    it('can be constructed', function () {
        expect(new TokenSettings(null, null, null))->toBeInstanceOf(TokenSettings::class);
    });

    it('can generate a style string with foreground', function () {
        $settings = new TokenSettings(null, '#fff', null);

        expect($settings->toStyleString())->toBe('color: #fff;');
    });

    it('can generate a style string with background', function () {
        $settings = new TokenSettings('#000', null, null);

        expect($settings->toStyleString())->toBe('background-color: #000;');
    });

    it('can generate a style string with font style italic', function () {
        $settings = new TokenSettings(null, null, 'italic');

        expect($settings->toStyleString())->toBe('font-style: italic;');
    });

    it('can generate a style string with font style bold', function () {
        $settings = new TokenSettings(null, null, 'bold');

        expect($settings->toStyleString())->toBe('font-weight: bold;');
    });

    it('can generate a style string with font style underline', function () {
        $settings = new TokenSettings(null, null, 'underline');

        expect($settings->toStyleString())->toBe('text-decoration: underline;');
    });

    it('can generate a style string with font style strikethrough', function () {
        $settings = new TokenSettings(null, null, 'strikethrough');

        expect($settings->toStyleString())->toBe('text-decoration: line-through;');
    });

    it('can generate a style string with multiple font styles', function () {
        $settings = new TokenSettings(null, null, 'italic underline');

        expect($settings->toStyleString())->toBe('font-style: italic;text-decoration: underline;');
    });

    it('can generate a style string with multiple font styles in any order', function () {
        $settings = new TokenSettings(null, null, 'underline italic');

        expect($settings->toStyleString())->toBe('text-decoration: underline;font-style: italic;');
    });

    it('can generate a style string with multiple font styles and foreground', function () {
        $settings = new TokenSettings(null, '#fff', 'underline italic');

        expect($settings->toStyleString())->toBe('color: #fff;text-decoration: underline;font-style: italic;');
    });

    it('can generate a style string with multiple font styles and background', function () {
        $settings = new TokenSettings('#000', null, 'underline italic');

        expect($settings->toStyleString())->toBe('background-color: #000;text-decoration: underline;font-style: italic;');
    });
});
