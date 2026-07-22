<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;

it('can generate html from ansi code using string grammar', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[31mred text\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    expect($html)->toContain('data-language="ansi"');
    expect($html)->toContain('language-ansi');
});

it('can generate html from ansi code using Grammar enum', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[32mgreen text\x1b[0m",
        Grammar::Ansi,
        Theme::GithubDark,
    )->toString();

    expect($html)->toContain('data-language="ansi"');
});

it('can use terminal alias for ansi', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[34mblue text\x1b[0m",
        'terminal',
        Theme::GithubDark,
    )->toString();

    expect($html)->toContain('data-language="ansi"');
});

it('applies foreground color styles', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[31mred\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    // Should contain a color style
    expect($html)->toMatch('/color:\s*#[0-9a-f]{6}/i');
});

it('applies font style for bold', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[1mbold\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    expect($html)->toContain('font-weight: bold');
});

it('applies font style for italic', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[3mitalic\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    expect($html)->toContain('font-style: italic');
});

it('applies font style for underline', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[4munderline\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    expect($html)->toContain('text-decoration: underline');
});

it('handles multiline ansi output', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[31mLine 1\x1b[0m\n\x1b[32mLine 2\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    // Should have multiple line spans
    expect(substr_count($html, 'class="line"'))->toBe(2);
});

it('handles 256-color mode', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[38;5;196mcolor 196\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    // Color 196 is bright red (#ff0000)
    expect($html)->toContain('#ff0000');
});

it('handles RGB color mode', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[38;2;128;64;32mRGB\x1b[0m",
        'ansi',
        Theme::GithubDark,
    )->toString();

    expect($html)->toContain('#804020');
});

it('works with multiple themes', function () {
    $html = (new Phiki)->codeToHtml(
        "\x1b[31mred\x1b[0m",
        'ansi',
        ['light' => Theme::GithubLight, 'dark' => Theme::GithubDark],
    )->toString();

    expect($html)->toContain('github-light');
    expect($html)->toContain('github-dark');
});
