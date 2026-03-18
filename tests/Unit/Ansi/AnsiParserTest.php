<?php

use Phiki\Ansi\AnsiParser;
use Phiki\Ansi\IndexedAnsiColor;
use Phiki\Ansi\NamedAnsiColor;
use Phiki\Ansi\RgbAnsiColor;

it('parses plain text without escape sequences', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse('Hello, World!');

    expect($tokens)->toHaveCount(1);
    expect($tokens[0]->text)->toBe('Hello, World!');
    expect($tokens[0]->foreground)->toBeNull();
    expect($tokens[0]->background)->toBeNull();
    expect($tokens[0]->decorations)->toBe([]);
});

it('parses standard foreground colors', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[31mred text\x1b[0m");

    expect($tokens)->toHaveCount(1);
    expect($tokens[0]->text)->toBe('red text');
    expect($tokens[0]->foreground)->toBeInstanceOf(NamedAnsiColor::class);
    expect($tokens[0]->foreground->name)->toBe('red');
});

it('parses bright foreground colors', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[91mbright red\x1b[0m");

    expect($tokens[0]->foreground)->toBeInstanceOf(NamedAnsiColor::class);
    expect($tokens[0]->foreground->name)->toBe('brightRed');
});

it('parses standard background colors', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[44mblue background\x1b[0m");

    expect($tokens[0]->background)->toBeInstanceOf(NamedAnsiColor::class);
    expect($tokens[0]->background->name)->toBe('blue');
});

it('parses 256-color mode', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[38;5;196mcolor 196\x1b[0m");

    expect($tokens[0]->foreground)->toBeInstanceOf(IndexedAnsiColor::class);
    expect($tokens[0]->foreground->index)->toBe(196);
});

it('parses RGB color mode', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[38;2;255;128;64mRGB color\x1b[0m");

    expect($tokens[0]->foreground)->toBeInstanceOf(RgbAnsiColor::class);
    expect($tokens[0]->foreground->r)->toBe(255);
    expect($tokens[0]->foreground->g)->toBe(128);
    expect($tokens[0]->foreground->b)->toBe(64);
});

it('parses bold decoration', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[1mbold text\x1b[0m");

    expect($tokens[0]->decorations)->toContain('bold');
});

it('parses italic decoration', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[3mitalic text\x1b[0m");

    expect($tokens[0]->decorations)->toContain('italic');
});

it('parses underline decoration', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[4munderlined text\x1b[0m");

    expect($tokens[0]->decorations)->toContain('underline');
});

it('parses strikethrough decoration', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[9mstrikethrough text\x1b[0m");

    expect($tokens[0]->decorations)->toContain('strikethrough');
});

it('parses multiple decorations', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[1;3;4mbold italic underline\x1b[0m");

    expect($tokens[0]->decorations)->toContain('bold');
    expect($tokens[0]->decorations)->toContain('italic');
    expect($tokens[0]->decorations)->toContain('underline');
});

it('parses combined color and decoration', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[1;31mbold red\x1b[0m");

    expect($tokens[0]->foreground)->toBeInstanceOf(NamedAnsiColor::class);
    expect($tokens[0]->foreground->name)->toBe('red');
    expect($tokens[0]->decorations)->toContain('bold');
});

it('handles reset correctly', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[31mred\x1b[0m normal");

    expect($tokens)->toHaveCount(2);
    expect($tokens[0]->foreground)->not->toBeNull();
    expect($tokens[1]->foreground)->toBeNull();
    expect($tokens[1]->text)->toBe(' normal');
});

it('handles color reset without full reset', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[31mred\x1b[39m default");

    expect($tokens[0]->foreground)->not->toBeNull();
    expect($tokens[1]->foreground)->toBeNull();
});

it('parses dim decoration', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[2mdim text\x1b[0m");

    expect($tokens[0]->decorations)->toContain('dim');
});

it('parses reverse decoration', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[7mreversed\x1b[0m");

    expect($tokens[0]->decorations)->toContain('reverse');
});

it('handles empty escape sequence as reset', function () {
    $parser = new AnsiParser;
    $tokens = $parser->parse("\x1b[31mred\x1b[m normal");

    expect($tokens[1]->foreground)->toBeNull();
});
