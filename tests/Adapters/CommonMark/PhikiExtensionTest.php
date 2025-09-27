<?php

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Theme\Theme;

it('registers renderers', function () {
    $environment = new Environment;

    $environment
        ->addExtension(new CommonMarkCoreExtension)
        ->addExtension(new PhikiExtension('github-dark'));

    $markdown = new MarkdownConverter($environment);
    $generated = $markdown->convert(<<<'MD'
    ```php
    class A {}
    ```
    MD)->getContent();

    expect($generated)
        ->toContain('phiki')
        ->toContain('github-dark')
        ->toContain('<span class="token" style="color: #b392f0;">A</span>');
});

it('can be configured using environment config array', function () {
    $environment = new Environment([
        'phiki' => [
            'theme' => Theme::GithubLight,
            'with_gutter' => false,
        ],
    ]);

    $environment
        ->addExtension(new CommonMarkCoreExtension)
        ->addExtension(new PhikiExtension);
    $markdown = new MarkdownConverter($environment);

    $generated = $markdown->convert(<<<'MD'
    ```php
    class A {}
    ```
    MD)->getContent();

    expect($generated)->toMatchSnapshot();
});

it('understands the info string', function () {
    $environment = new Environment;

    $environment
        ->addExtension(new CommonMarkCoreExtension)
        ->addExtension(new PhikiExtension('github-dark'));

    $markdown = new MarkdownConverter($environment);

    $generated = $markdown->convert(<<<'MD'
    ```php {0-10}
    class A {}
    ```
    MD);

    expect($generated)->toMatchSnapshot();
});

it('falls back to txt if the grammar doesnt exist', function () {
    $environment = new Environment;

    $environment
        ->addExtension(new CommonMarkCoreExtension)
        ->addExtension(new PhikiExtension('github-dark'));

    $markdown = new MarkdownConverter($environment);

    $generated = $markdown->convert(<<<'MD'
    ```nonexistentlang
    class A {}
    ```
    MD);

    expect($generated)->toMatchSnapshot();
});

it('shows diff symbols in gutter for insert and remove annotations', function () {
    $environment = new Environment;

    $environment
        ->addExtension(new CommonMarkCoreExtension)
        ->addExtension(new PhikiExtension(Theme::GithubLight, withGutter: true));

    $markdown = new MarkdownConverter($environment);

    $generated = $markdown->convert(<<<'MD'
    ```php
    echo 'Normal line';
    echo 'Remove line'; // [code! remove]
    echo 'Insert line'; // [code! insert]
    echo 'Another normal line';
    ```
    MD);

    $content = $generated->getContent();

    // Check that normal lines show numbers
    expect($content)->toContain('<span class="line-number"'); // Has line numbers
    expect($content)->toContain('> 1</span>'); // First line shows number
    expect($content)->toContain('> 4</span>'); // Last line shows number

    // Check that diff lines show symbols
    expect($content)->toContain('> -</span>'); // Remove line shows -
    expect($content)->toContain('> +</span>'); // Insert line shows +

    // Ensure diff lines have proper classes
    expect($content)->toContain('<span class="line remove">');
    expect($content)->toContain('<span class="line insert">');
});
