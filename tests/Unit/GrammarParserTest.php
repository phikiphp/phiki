<?php

use Phiki\GrammarParser;

describe('GrammarParser', function () {
    it('can be constructed', function () {
        expect(new GrammarParser)->toBeInstanceOf(GrammarParser::class);
    });

    it('can parse a grammar file', function () {
        $parser = new GrammarParser;
        $grammar = $parser->parse(json_decode(file_get_contents(__DIR__ . '/../../languages/php.json'), true));

        expect($grammar->scopeName)->toBe('source.php');
        expect($grammar->patterns)->toBeArray();
        expect($grammar->repository)->toBeArray();
    })->only();
});