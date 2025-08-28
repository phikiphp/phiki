<?php

use Phiki\Adapters\CommonMark\Transformers\Annotations\AnnotationType;
use Phiki\Grammar\Grammar;
use Phiki\Theme\Theme;

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
