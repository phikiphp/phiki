<?php

use Phiki\Grammar\Grammar;
use Phiki\Phast\ClassList;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Phiki\Transformers\Decorations\CodeDecoration;
use Phiki\Transformers\Decorations\GutterDecoration;
use Phiki\Transformers\Decorations\LineDecoration;
use Phiki\Transformers\Decorations\PreDecoration;

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

it('can apply decorations to pre', function () {
    $output = (new Phiki)
        ->codeToHtml(<<<'PHP'
        echo "Hello, world!";
        PHP, Grammar::Php, Theme::GithubLight)
        ->decoration(new PreDecoration(new ClassList(['pre-class'])))
        ->toString();

    expect($output)->toContain('<pre class="phiki language-php github-light pre-class" data-language="php"');
});

it('can apply decorations to code', function () {
    $output = (new Phiki)
        ->codeToHtml(<<<'PHP'
        echo "Hello, world!";
        PHP, Grammar::Php, Theme::GithubLight)
        ->decoration(new CodeDecoration(new ClassList(['code-class'])))
        ->toString();

    expect($output)->toContain('<code class="code-class">');
});

it('can apply decorations to gutter', function () {
    $output = (new Phiki)
        ->codeToHtml(<<<'PHP'
        echo "Hello, world!";
        PHP, Grammar::Php, Theme::GithubLight, true)
        ->withGutter()
        ->decoration(GutterDecoration::make()->class('gutter-class'))
        ->toString();

    expect($output)->toContain('<span class="line-number gutter-class"');
});
