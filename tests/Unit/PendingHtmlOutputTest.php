<?php

use Phiki\Adapters\CommonMark\Transformers\AnnotationsTransformer;
use Phiki\Adapters\CommonMark\Transformers\MetaTransformer;
use Phiki\Grammar\Grammar;
use Phiki\Output\Html\PendingHtmlOutput;
use Phiki\Phast\Element;
use Phiki\Phiki;
use Phiki\Tests\Fixtures\FakeCache;
use Phiki\Tests\Fixtures\UselessTransformer;
use Phiki\Theme\Theme;
use Phiki\Transformers\AbstractTransformer;
use Phiki\Transformers\AddClassesTransformer;
use Phiki\Transformers\Decorations\LineDecoration;
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

it('does not share cached HTML between outputs with different options', function (Closure $first, Closure $second) {
    $phiki = (new Phiki)->cache(new FakeCache);
    $render = fn (Closure $configure) => $configure($phiki->codeToHtml("echo 1;\necho 2;", Grammar::Php, Theme::GithubLight))->toString();

    $uncached = $second((new Phiki)->codeToHtml("echo 1;\necho 2;", Grammar::Php, Theme::GithubLight))->toString();

    expect($render($first))->not->toBe($uncached);
    expect($render($second))->toBe($uncached);
})->with([
    'meta' => [
        fn (PendingHtmlOutput $output) => $output->withMeta(new Meta(markdownInfo: '{1}'))->transformer(new MetaTransformer),
        fn (PendingHtmlOutput $output) => $output->withMeta(new Meta(markdownInfo: '{2}'))->transformer(new MetaTransformer),
    ],
    'transformer options' => [
        fn (PendingHtmlOutput $output) => $output->transformer(new AddClassesTransformer),
        fn (PendingHtmlOutput $output) => $output->transformer(new AddClassesTransformer(styles: false)),
    ],
    'decorations' => [
        fn (PendingHtmlOutput $output) => $output->decoration(LineDecoration::forLine(0)->class('one')),
        fn (PendingHtmlOutput $output) => $output->decoration(LineDecoration::forLine(0)->class('two')),
    ],
]);

it('reads from cache when transformers store state while rendering', function () {
    $cache = new FakeCache;
    $code = "echo 1; // [code! highlight]\necho 2;";

    $pending = fn () => (new Phiki)->codeToHtml($code, Grammar::Php, Theme::GithubLight)->transformer(new AnnotationsTransformer)->cache($cache);

    $first = $pending();
    $key = $first->cacheKey();

    expect($first->toString())->toContain('highlight');
    expect($first->cacheKey())->toBe($key);

    $cache->set($key, 'cached');

    expect($pending()->toString())->toBe('cached');
});

it('does not cache HTML from transformers that cannot be serialized', function () {
    $cache = new FakeCache;

    $transformer = new class(fn () => 'one') extends AbstractTransformer
    {
        public function __construct(private Closure $class) {}

        public function pre(Element $pre): Element
        {
            $pre->properties->get('class')->add(($this->class)());

            return $pre;
        }
    };

    $pending = (new Phiki)->codeToHtml('echo 1;', Grammar::Php, Theme::GithubLight)->transformer($transformer)->cache($cache);
    $pending->toString();

    expect($cache->has($pending->cacheKey()))->toBeFalse();
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

it('renders the same html with and without transformers', function (bool $withGutter) {
    $code = <<<'PHP'
    function add(int $a, int $b): int {
        return $a + $b;
    }

    echo add(1, 2);
    PHP;

    $render = function (bool $withTransformer) use ($code, $withGutter) {
        $output = (new Phiki)
            ->codeToHtml($code, Grammar::Php, ['light' => Theme::GithubLight, 'dark' => Theme::GithubDark])
            ->withGutter($withGutter);

        if ($withTransformer) {
            $output->transformer(new UselessTransformer);
        }

        return $output->toString();
    };

    expect($render(false))->toBe($render(true));
})->with(['without gutter' => false, 'with gutter' => true]);

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
