<?php

use Phiki\Grammar\Grammar;
use Phiki\Theme\Theme;

describe('highlight', function () {
    it('can highlight a single line', function () {
        $output = markdown('echo "Hello, world!"; // [code! highlight]', Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a fixed set of lines', function () {
        $output = markdown(<<<'PHP'
        echo "Hello, world!"; // [code! highlight:2]
        echo "This line is highlighted.";
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a fixed negative range of lines', function () {
        $output = markdown(<<<'PHP'
        echo "This line is not highlighted!";
        echo "This line is highlighted.";
        echo "This is also highlighted.";
        echo "This line is highlighted."; // [code! highlight:-2]
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a range of lines in the middle of a block', function () {
        $output = markdown(<<<'PHP'
        echo "This line is not highlighted!";
        echo "This line is highlighted."; // [code! highlight:1]
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a range with negative offset', function () {
        $output = markdown(<<<'PHP'
        echo "This line is not highlighted!";
        echo "This line is highlighted.";
        echo "This is also highlighted."; // [code! highlight:-1]
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight with an open ended range', function () {
        $output = markdown(<<<'PHP'
        echo "This line is not highlighted!";
        echo "This line is highlighted."; // [code! highlight:start]
        echo "This is also highlighted."; // [code! highlight:end]
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight an offset with total', function () {
        $output = markdown(<<<'PHP'
        echo "This line is not highlighted!"; // [code! highlight:1,2]
        echo "This line is highlighted.";
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });

    it('can highlight a negative offset with total', function () {
        $output = markdown(<<<'PHP'
        echo "This line is highlighted!";
        echo "This line is highlighted."; // [code! highlight:-1,3]
        echo "This is also highlighted.";
        echo "This line is not highlighted.";
        PHP, Theme::GithubLight, Grammar::Php);

        expect($output)->toMatchSnapshot();
    });
});

describe('focus', function () {
    it('can focus a line', function () {
        $output = markdown(<<<'PHP'
        echo "This line is not focused!";
        echo "This line is focused."; // [code! focus]
        echo "This is also not focused.";
        PHP, Theme::GithubLight, Grammar::Php);

        echo $output;

        expect($output)->toMatchSnapshot();
    });
});
