<?php

use Phiki\Adapters\CommonMark\Transformers\Annotations\AnnotationType;
use Phiki\Adapters\CommonMark\Transformers\AnnotationsTransformer;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\GrammarRepository;
use Phiki\Phast\ClassList;
use Phiki\Phast\Element;
use Phiki\Phast\Properties;
use Phiki\Theme\Theme;
use Phiki\Theme\ThemeRepository;

describe('highlight', function () {
    it('can highlight a single line', function (string $keyword) {
        $output = markdown("echo 'Hello, world!'; // [code! {$keyword}]", Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a fixed set of lines', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "Hello, world!"; // [code! {$keyword}:2]
        echo "This line is highlighted.";
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a fixed negative range of lines', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not highlighted!";
        echo "This line is highlighted.";
        echo "This is also highlighted.";
        echo "This line is highlighted."; // [code! {$keyword}:-2]
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a range of lines in the middle of a block', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not highlighted!";
        echo "This line is highlighted."; // [code! {$keyword}:1]
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a range with negative offset', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not highlighted!";
        echo "This line is highlighted.";
        echo "This is also highlighted."; // [code! {$keyword}:-1]
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight with an open ended range', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not highlighted!";
        echo "This line is highlighted."; // [code! {$keyword}:start]
        echo "This is also highlighted."; // [code! {$keyword}:end]
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight an offset with total', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not highlighted!"; // [code! {$keyword}:1,2]
        echo "This line is highlighted.";
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a negative offset with total', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is highlighted!";
        echo "This line is highlighted."; // [code! {$keyword}:-1,3]
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });
})->with(AnnotationType::Highlight->keywords());

describe('focus', function () {
    it('can focus a line', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not focused!";
        echo "This line is focused."; // [code! {$keyword}]
        echo "This is also not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can focus multiple lines', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not focused!";
        echo "This line is focused."; // [code! {$keyword}:2]
        echo "This is also focused.";
        echo "This line is not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can focus a negative offset', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not focused!";
        echo "This line is focused.";
        echo "This is also focused."; // [code! {$keyword}:-2]
        echo "This line is not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can focus a range of lines', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not focused!";
        echo "This line is focused."; // [code! {$keyword}:1]
        echo "This is also focused.";
        echo "This line is not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can focus a negative offset range', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not focused!";
        echo "This line is focused.";
        echo "This is also focused."; // [code! {$keyword}:-1]
        echo "This line is not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can focus an open ended range', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not focused!";
        echo "This line is focused."; // [code! {$keyword}:start]
        echo "This is also focused."; // [code! {$keyword}:end]
        echo "This line is not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can focus an offset with total', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not focused!"; // [code! {$keyword}:1,2]
        echo "This line is focused.";
        echo "This is also focused.";
        echo "This line is not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can focus a negative offset with total', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is focused!";
        echo "This line is focused."; // [code! {$keyword}:-1,3]
        echo "This is also focused.";
        echo "This line is not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });
})->with(AnnotationType::Focus->keywords());

describe('insert', function () {
    it('can mark a single line as inserted', function (string $keyword) {
        $output = markdown("echo 'Hello, world!'; // [code! {$keyword}]", Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a fixed set of lines as inserted', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "Hello, world!"; // [code! {$keyword}:2]
        echo "This line is inserted.";
        echo "This is also inserted.";
        echo "This line is not inserted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a fixed negative range of lines as inserted', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not inserted!";
        echo "This line is inserted.";
        echo "This is also inserted.";
        echo "This line is inserted."; // [code! {$keyword}:-2]
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a range of lines in the middle of a block as inserted', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not inserted!";
        echo "This line is inserted."; // [code! {$keyword}:1]
        echo "This is also inserted.";
        echo "This line is not inserted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a range with negative offset as inserted', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not inserted!";
        echo "This line is inserted.";
        echo "This is also inserted."; // [code! {$keyword}:-1]
        echo "This line is not inserted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark with an open ended range as inserted', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not inserted!";
        echo "This line is inserted."; // [code! {$keyword}:start]
        echo "This is also inserted."; // [code! {$keyword}:end]
        echo "This line is not inserted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark an offset with total as inserted', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not inserted!"; // [code! {$keyword}:1,2]
        echo "This line is inserted.";
        echo "This is also inserted.";
        echo "This line is not inserted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a negative offset with total as inserted', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is inserted!";
        echo "This line is inserted."; // [code! {$keyword}:-1,3]
        echo "This is also inserted.";
        echo "This line is not inserted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });
})->with(AnnotationType::Insert->keywords());

describe('remove', function () {
    it('can mark a single line as removed', function (string $keyword) {
        $output = markdown("echo 'Hello, world!'; // [code! {$keyword}]", Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a fixed set of lines as removed', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "Hello, world!"; // [code! {$keyword}:2]
        echo "This line is removed.";
        echo "This is also removed.";
        echo "This line is not removed.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a fixed negative range of lines as removed', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not removed!";
        echo "This line is removed.";
        echo "This is also removed.";
        echo "This line is removed."; // [code! {$keyword}:-2]
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a range of lines in the middle of a block as removed', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not removed!";
        echo "This line is removed."; // [code! {$keyword}:1]
        echo "This is also removed.";
        echo "This line is not removed.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a range with negative offset as removed', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not removed!";
        echo "This line is removed.";
        echo "This is also removed."; // [code! {$keyword}:-1]
        echo "This line is not removed.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark with an open ended range as removed', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not removed!";
        echo "This line is removed."; // [code! {$keyword}:start]
        echo "This is also removed."; // [code! {$keyword}:end]
        echo "This line is not removed.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark an offset with total as removed', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is not removed!"; // [code! {$keyword}:1,2]
        echo "This line is removed.";
        echo "This is also removed.";
        echo "This line is not removed.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can mark a negative offset with total as removed', function (string $keyword) {
        $output = markdown(<<<PHP
        echo "This line is removed!";
        echo "This line is removed."; // [code! {$keyword}:-1,3]
        echo "This is also removed.";
        echo "This line is not removed.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });
})->with(AnnotationType::Remove->keywords());

describe('theme css variables', function () {
    it('terminates an existing style before appending its variables', function () {
        $transformer = new AnnotationsTransformer;
        $transformer->withGrammar(Grammar::Php->toParsedGrammar(new GrammarRepository));
        $transformer->withThemes(['default' => Theme::GithubLight->toParsedTheme(new ThemeRepository)]);
        $transformer->preprocess('echo "Hello, world!"; // [code! highlight]');

        $pre = new Element('pre', new Properties(['class' => new ClassList, 'style' => 'border: 1px solid red']));

        expect($transformer->pre($pre)->properties->get('style'))
            ->toStartWith('border: 1px solid red;--phiki-line-highlight:')
            ->toEndWith(';')
            ->not->toContain(';;');
    });

    it('appends its variables to an already terminated style', function () {
        $transformer = new AnnotationsTransformer;
        $transformer->withGrammar(Grammar::Php->toParsedGrammar(new GrammarRepository));
        $transformer->withThemes(['default' => Theme::GithubLight->toParsedTheme(new ThemeRepository)]);
        $transformer->preprocess('echo "Hello, world!"; // [code! highlight]');

        $pre = new Element('pre', new Properties(['class' => new ClassList, 'style' => 'border: 1px solid red;']));

        expect($transformer->pre($pre)->properties->get('style'))
            ->toStartWith('border: 1px solid red;--phiki-line-highlight:')
            ->not->toContain(';;');
    });
});

describe('multibyte', function () {
    it('does not corrupt multibyte characters that contain line-separator bytes', function () {
        // "ą" is U+0105 (0xC4 0x85). Splitting lines without the `u` modifier makes
        // `\R` match the trailing 0x85 byte as a NEL character, cutting the string
        // mid-character and producing invalid UTF-8. See #143.
        $output = markdown('echo "połączenia";', Theme::GithubLight, Grammar::Php);

        expect($output)->toContain('połączenia');
    });

    it('handles multibyte characters on annotated lines', function () {
        $output = markdown(<<<'PHP'
        echo "połączenia"; // [code! highlight]
        echo "zażółć gęślą jaźń";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)
            ->toContain('połączenia')
            ->toContain('zażółć gęślą jaźń')
            ->not->toContain('[code! highlight]');
    });
});
