<?php

use Phiki\Grammar\Grammar;
use Phiki\Phast\ClassList;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Phiki\Transformers\Decorations\LineDecoration;

it('can apply decorations to a line', function () {
    $output = (new Phiki)
        ->codeToHtml(<<<'PHP'
        echo "Hello, world!";
        PHP, Grammar::Php, Theme::GithubLight)
        ->decoration(new LineDecoration(0, new ClassList(['test-class'])))
        ->toString();

    expect($output)->toContain('<span class="line test-class">');
});

it('can apply decorations to multiple lines with multiple instances', function () {
    $output = (new Phiki)
        ->codeToHtml(<<<'PHP'
        echo "Hello, world!";
        echo "Goodbye, world!";
        PHP, Grammar::Php, Theme::GithubLight)
        ->decoration(
            new LineDecoration(0, new ClassList(['first-line'])),
            new LineDecoration(1, new ClassList(['second-line'])),
            new LineDecoration(0, new ClassList(['also-first-line'])),
        )
        ->toString();

    expect($output)
        ->toContain('<span class="line first-line also-first-line">')
        ->toContain('<span class="line second-line">');
});

it('can apply decorations to a range of lines', function () {
    $output = (new Phiki)
        ->codeToHtml(<<<'PHP'
        echo "Hello, world!";
        echo "Goodbye, world!";
        echo "Farewell, world!";
        PHP, Grammar::Php, Theme::GithubLight)
        ->decoration(new LineDecoration([0, 2], new ClassList(['multi-line'])))
        ->toString();

    expect(substr_count($output, 'multi-line'))->toBe(3);
});
