<?php

use Phiki\Adapters\Laravel\Facades\Phiki;
use Phiki\Environment;
use Phiki\Grammar\Grammar;
use Phiki\Output\Html\PendingHtmlOutput;
use Phiki\Tests\Fixtures\EmptyExtension;
use Phiki\Theme\Theme;

it('can return the environment object', function () {
    expect(Phiki::environment())->toBeInstanceOf(Environment::class);
});

it('can register a custom grammar', function () {
    Phiki::grammar('custom', __DIR__ . '/../../../Fixtures/example.json');

    expect(Phiki::environment()->grammars->has('custom'))->toBeTrue();
});

it('can register a custom theme', function () {
    Phiki::theme('custom', __DIR__ . '/../../../Fixtures/theme.json');

    expect(Phiki::environment()->themes->has('custom'))->toBeTrue();
});

it('can register an extension', function () {
    $extension = new EmptyExtension();

    Phiki::extend($extension);

    expect($extension->registered)->toBeTrue();
});

it('can convert code to tokens', function () {
    $tokens = Phiki::codeToTokens('<?php echo "Hello, World!";', Grammar::Php);

    expect($tokens)->toBeArray();
});

it('can convert tokens to highlighted tokens', function () {
    $tokens = Phiki::codeToTokens('<?php echo "Hello, World!";', Grammar::Php);
    $highlightedTokens = Phiki::tokensToHighlightedTokens($tokens, Theme::GithubLight);

    expect($highlightedTokens)->toBeArray();
});

it('can convert code to highlighted tokens', function () {
    $highlightedTokens = Phiki::codeToHighlightedTokens('<?php echo "Hello, World!";', Grammar::Php, Theme::GithubLight);

    expect($highlightedTokens)->toBeArray();
});

it('can convert code to HTML', function () {
    $htmlOutput = Phiki::codeToHtml('<?php echo "Hello, World!";', Grammar::Php, Theme::GithubLight);

    expect($htmlOutput)->toBeInstanceOf(PendingHtmlOutput::class);
});
