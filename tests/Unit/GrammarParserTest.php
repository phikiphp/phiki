<?php

use Phiki\Grammar\GrammarParser;

describe('GrammarParser', function () {
    it('can be constructed', function () {
        expect(new GrammarParser)->toBeInstanceOf(GrammarParser::class);
    });

    it('can parse a grammar file', function () {
        $parser = new GrammarParser;
        $grammar = $parser->parse(json_decode(file_get_contents(__DIR__.'/../../resources/languages/php.json'), true));

        expect($grammar->scopeName)->toBe('source.php');
        expect($grammar->patterns)->toBeArray();
        expect($grammar->repository)->toBeArray();
    });

    it('can parse a grammar file with injections', function () {
        $parser = new GrammarParser;
        $grammar = $parser->parse(json_decode(file_get_contents(__DIR__.'/../../resources/languages/blade.json'), true));

        expect($grammar->scopeName)->toBe('text.html.php.blade');
        expect($grammar->getInjections())->toBeArray();
        expect($grammar->hasInjections())->toBeTrue();
    });

    it('marks injection patterns as injected ones', function () {
        $parser = new GrammarParser;
        $grammar = $parser->parse(json_decode(file_get_contents(__DIR__.'/../../resources/languages/blade.json'), true));

        expect($grammar->scopeName)->toBe('text.html.php.blade');
        expect($grammar->getInjections()[0]->pattern->injection)->toBeTrue();
    });
});
