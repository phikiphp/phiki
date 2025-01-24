<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;

describe('Phiki', function () {
    it('can be constructed', function () {
        expect(new Phiki)->toBeInstanceOf(Phiki::class);
    });

    it('can generate html from code', function () {
        expect((new Phiki)->codeToHtml(<<<'PHP'
        function add(int|float $a, int|float $b): int|float {
            return $a + $b;
        }
        PHP, 'php', 'github-dark'))->toBeString();
    });

    it('adds a language data property and class if grammar has a name', function () {
        $html = (new Phiki)->codeToHtml(<<<'PHP'
        function add(int|float $a, int|float $b): int|float {
            return $a + $b;
        }
        PHP, 'php', 'github-dark');

        expect($html)->toContain('data-language="php"');
        expect($html)->toContain('language-php');
    });

    it('does not add a language data property and class if grammar has no name', function () {
        $html = (new Phiki)->codeToHtml(<<<'PHP'
        function add(int|float $a, int|float $b): int|float {
            return $a + $b;
        }
        PHP, Grammar::Txt, 'github-dark');

        expect($html)->not->toContain('data-language');
        expect($html)->not->toContain('language-');
    });

    it('adds data-line attributes to each line', function () {
        $html = (new Phiki)->codeToHtml(<<<'PHP'
        function add(int|float $a, int|float $b): int|float {
            return $a + $b;
        }
        PHP, 'php', 'github-dark');

        expect($html)->toContain('data-line="1"');
        expect($html)->toContain('data-line="2"');
        expect($html)->toContain('data-line="3"');
    });

    it('can generate code with multiple themes', function () {
        $code = (new Phiki)->codeToHtml(<<<'PHP'
        echo "Hello, world";
        PHP, Grammar::Php, ['light' => Theme::GithubLight, 'dark' => Theme::GithubDark]);

        expect($code)->toContain('github-light')->toContain('github-dark');
        expect($code)->toContain('--phiki-dark-color');
        expect($code)->toContain('--phiki-dark-background-color');
    });

    it('accepts a grammar enum member', function () {
        expect((new Phiki)->codeToTokens('echo $a;', Grammar::Php))->toBeArray();
    });

    it('accepts a theme enum member', function () {
        expect((new Phiki)->codeToHtml('echo $a;', Grammar::Php, Theme::GithubDark))->toBeString();
    });

    it('can detect the grammar of code', function () {
        expect((new Phiki)->detectGrammar('<?php echo "Hello, world";'))->toBe(Grammar::Php);
        expect((new Phiki)->detectGrammar('console.log("Hello, world");'))->toBe(Grammar::Javascript);
    });
});
