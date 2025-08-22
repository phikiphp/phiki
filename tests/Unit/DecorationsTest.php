<?php

use Phiki\Decorations\Decoration;
use Phiki\Decorations\DecorationLocation;
use Phiki\Grammar\Grammar;
use Phiki\Phast\ClassList;
use Phiki\Phast\Properties;
use Phiki\Phiki;
use Phiki\Theme\Theme;

it('can add decorations to an entire line', function () {
    $output = (new Phiki)
        ->codeToHtml(
            <<<'PHP'
            echo "Hello, world!";

            class Foo {}
            PHP,
            Grammar::Php,
            Theme::GithubLight,
        )
        ->decoration(new Decoration(
            (new Properties)
                ->set('class', new ClassList(['highlighted-line'])),
            start: new DecorationLocation(line: 1),
        ))
        ->toString();

    dd($output);
});
