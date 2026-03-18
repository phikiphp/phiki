<?php

use Phiki\Ansi\AnsiPalette;
use Phiki\Ansi\AnsiToken;
use Phiki\Ansi\AnsiTokenizer;

it('tokenizes plain text', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize('Hello, World!');

    expect($lines)->toHaveCount(1);
    expect($lines[0])->toHaveCount(1);
    expect($lines[0][0])->toBeInstanceOf(AnsiToken::class);
    expect($lines[0][0]->text)->toBe('Hello, World!');
});

it('tokenizes multiline text', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("Line 1\nLine 2\nLine 3");

    expect($lines)->toHaveCount(3);
});

it('resolves named colors to hex', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("\x1b[31mred\x1b[0m");

    expect($lines[0][0]->foreground)->toBe('#cd3131');
});

it('resolves RGB colors to hex', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("\x1b[38;2;255;128;64mRGB\x1b[0m");

    expect($lines[0][0]->foreground)->toBe('#ff8040');
});

it('handles bold font style', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("\x1b[1mbold\x1b[0m");

    expect($lines[0][0]->fontStyle)->toBe('bold');
});

it('handles multiple font styles', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("\x1b[1;3mbold italic\x1b[0m");

    expect($lines[0][0]->fontStyle)->toBe('bold italic');
});

it('handles reverse by swapping fg/bg', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#ffffff', '#000000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("\x1b[31;7mreversed red\x1b[0m");

    // Red foreground reversed becomes red background
    expect($lines[0][0]->background)->toBe('#cd3131');
    // Default fg (#ffffff) becomes foreground
    expect($lines[0][0]->foreground)->toBe('#000000');
});

it('handles dim by reducing brightness', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("\x1b[31;2mdim red\x1b[0m");

    // Red (#cd3131) with 50% alpha
    expect($lines[0][0]->foreground)->toBe('#cd313180');
});

it('creates empty token for empty lines', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');
    $tokenizer = new AnsiTokenizer($palette);

    $lines = $tokenizer->tokenize("Line 1\n\nLine 3");

    expect($lines)->toHaveCount(3);
    expect($lines[1][0]->text)->toBe('');
});
