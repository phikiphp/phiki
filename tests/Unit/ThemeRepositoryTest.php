<?php

use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;
use Phiki\Theme\ThemeRepository;

describe('ThemeRepository', function () {
    it('can be constructed', function () {
        expect(new ThemeRepository)->toBeInstanceOf(ThemeRepository::class);
    });

    it('can check for the existence of a grammar', function () {
        $themeRepository = new ThemeRepository;

        expect($themeRepository->has('github-dark'))->toBeTrue();
    });

    it('can get a grammar', function () {
        $themeRepository = new ThemeRepository;
        $grammar = $themeRepository->get('github-dark');

        expect($grammar)
            ->toBeInstanceOf(ParsedTheme::class);
    });

    it('can register a custom grammar using a file path', function () {
        $themeRepository = new ThemeRepository;
        $themeRepository->register('example', __DIR__.'/../Fixtures/theme.json');

        $grammar = $themeRepository->get('example');

        expect($grammar)
            ->toBeInstanceOf(ParsedTheme::class);
    });

    it('can register a custom grammar using a grammar array', function () {
        $themeRepository = new ThemeRepository;
        $themeRepository->register('example', Theme::parse([
            'name' => 'example',
            'colors' => [
                'editor.background' => '#000000',
                'editor.foreground' => '#ffffff',
            ],
            'tokenColors' => [],
        ]));

        $grammar = $themeRepository->get('example');

        expect($grammar)
            ->toBeInstanceOf(ParsedTheme::class);
    });
});
