<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Phiki\Transformers\AddClassesTransformer;

it('adds classes to each token element', function () {
    $output = (new Phiki)
        ->codeToHtml(
            <<<'PHP'
            echo "Hello, world!";
            PHP,
            Grammar::Php,
            Theme::GithubLight,
        )
        ->transformer(new AddClassesTransformer())
        ->toString();

    expect($output)->toContain("phiki-source.php");
});

it('can remove the style attribute', function () {
    $output = (new Phiki)
        ->codeToHtml(
            <<<'PHP'
            echo "Hello, world!";
            PHP,
            Grammar::Php,
            Theme::GithubLight,
        )
        ->transformer(new AddClassesTransformer(retainStyles: false))
        ->toString();

    expect($output)->toContain('<span class="token phiki-source.php">');
});
