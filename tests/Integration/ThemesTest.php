<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\ThemeRepository;

pest()->group('integration/themes');

describe('Themes', function () {
    test('default themes do not produce warnings or exceptions', function (string $theme) {
        $sample = file_get_contents(__DIR__.'/../../resources/samples/sample.php');

        (new Phiki)->codeToHighlightedTokens($sample, Grammar::Php, $theme);
    })
        ->with('themes')
        ->throwsNoExceptions();
});

dataset('themes', function () {
    $repository = new ThemeRepository;
    $themes = $repository->getAllThemeNames();

    sort($themes, SORT_NATURAL);

    return $themes;
});
