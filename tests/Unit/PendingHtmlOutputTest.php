<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Tests\Fixtures\FakeCache;
use Phiki\Tests\Fixtures\UselessTransformer;
use Phiki\Theme\Theme;
use Phiki\Transformers\Meta;

it('calls transformer methods', function () {
    $transformer = new UselessTransformer;

    (new Phiki)->codeToHtml(
        <<<'PHP'
        echo "Hello, world!";
        PHP,
        Grammar::Php,
        Theme::GithubLight
    )
        ->transformer($transformer)
        ->__toString();

    expect($transformer->preprocessed)->toBeTrue();
    expect($transformer->tokens)->toBeTrue();
    expect($transformer->highlighted)->toBeTrue();
    expect($transformer->root)->toBeTrue();
    expect($transformer->pre)->toBeTrue();
    expect($transformer->code)->toBeTrue();
    expect($transformer->line)->toBeTrue();
    expect($transformer->token)->toBeTrue();
    expect($transformer->postprocessed)->toBeTrue();
});

it('can change the starting line number', function () {
    $html = (new Phiki)->codeToHtml(
        <<<'PHP'
        echo "Hello, world!";
        PHP,
        Grammar::Php,
        Theme::GithubLight,
    )
        ->withGutter()
        ->startingLine(10)
        ->toString();

    expect($html)->toContain('>10</span');
});

it('can output line numbers', function () {
    $html = (new Phiki)->codeToHtml(
        <<<'PHP'
        echo "Hello, world!";
        PHP,
        Grammar::Php,
        Theme::GithubLight,
    )
        ->withGutter()
        ->toString();

    expect($html)->toContain('> 1</span');
});

it('can cache the generated HTML', function () {
    $cache = new FakeCache;

    $pending = (new Phiki)
        ->codeToHtml(
            <<<'PHP'
            echo "Hello, world!";
            PHP,
            Grammar::Php,
            Theme::GithubLight,
        )
        ->cache($cache);

    $pending->toString();

    expect($cache->has($pending->cacheKey()))->toBeTrue();
});

it('can read from cache', function () {
    $cache = new FakeCache;

    $pending = (new Phiki)
        ->codeToHtml(
            <<<'PHP'
            echo "Hello, world!";
            PHP,
            Grammar::Php,
            Theme::GithubLight,
        )
        ->cache($cache);

    $pending->toString();

    $pending2 = (new Phiki)
        ->codeToHtml(
            <<<'PHP'
            echo "Hello, world!";
            PHP,
            Grammar::Php,
            Theme::GithubLight,
        )
        ->cache($cache);

    expect($pending2->toString())->toBe($pending->toString());
});

it('passes meta to transformers', function () {
    $transformer = new class extends UselessTransformer
    {
        public function meta(): Meta
        {
            return $this->meta;
        }
    };

    $output = (new Phiki)
        ->codeToHtml(
            <<<'PHP'
            echo "Hello, world!";
            PHP,
            Grammar::Php,
            Theme::GithubLight,
        )
        ->transformer($transformer)
        ->withMeta($meta = new Meta)
        ->toString();

    expect($transformer->meta())->toBe($meta);
});

describe('style attributes', function () {
    it('does not emit doubled separators or glued declarations with multiple themes', function () {
        $output = (string) (new Phiki)->codeToHtml('echo "Hello, world!";', Grammar::Php, [
            'light' => Theme::GithubLight,
            'dark' => Theme::GithubDark,
        ]);

        preg_match_all('/style="([^"]*)"/', $output, $matches);

        expect($matches[1])->not->toBeEmpty();

        foreach ($matches[1] as $style) {
            expect($style)->not->toContain(';;');

            // Every declaration must be a well-formed `property: value` pair. A glued
            // run like `#e1e4e8--theme-selection-background: #e2e5e9` would produce a
            // declaration with two colons in it. See #142.
            foreach (explode(';', rtrim($style, ';')) as $declaration) {
                expect($declaration)->toMatch('/^[^:]+: [^:]+$/');
            }
        }
    });

    it('terminates style attributes so appended declarations cannot glue on', function () {
        $output = (string) (new Phiki)->codeToHtml('echo "Hello, world!";', Grammar::Php, [
            'light' => Theme::GithubLight,
            'dark' => Theme::GithubDark,
        ]);

        preg_match_all('/style="([^"]*)"/', $output, $matches);

        foreach ($matches[1] as $style) {
            expect($style)->toEndWith(';');
        }
    });
});
