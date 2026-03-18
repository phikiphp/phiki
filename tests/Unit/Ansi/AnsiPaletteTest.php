<?php

use Phiki\Ansi\AnsiPalette;
use Phiki\Theme\ParsedTheme;

it('creates palette from theme with terminal colors', function () {
    $theme = new ParsedTheme('test', [
        'terminal.ansiRed' => '#ff0000',
        'terminal.ansiGreen' => '#00ff00',
        'editor.foreground' => '#ffffff',
        'editor.background' => '#000000',
    ]);

    $palette = AnsiPalette::fromTheme($theme);

    expect($palette->get('red'))->toBe('#ff0000');
    expect($palette->get('green'))->toBe('#00ff00');
    expect($palette->defaultForeground)->toBe('#ffffff');
    expect($palette->defaultBackground)->toBe('#000000');
});

it('falls back to defaults when theme lacks terminal colors', function () {
    $theme = new ParsedTheme('test', [
        'editor.foreground' => '#ffffff',
        'editor.background' => '#000000',
    ]);

    $palette = AnsiPalette::fromTheme($theme);

    expect($palette->get('red'))->toBe('#cd3131');
    expect($palette->get('brightRed'))->toBe('#f14c4c');
});

it('converts 256-color index 0-15 to named colors', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');

    expect($palette->indexToHex(0))->toBe('#000000'); // black
    expect($palette->indexToHex(1))->toBe('#cd3131'); // red
    expect($palette->indexToHex(9))->toBe('#f14c4c'); // bright red
});

it('converts 256-color cube colors', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');

    // Index 16 = RGB(0,0,0) in the cube
    expect($palette->indexToHex(16))->toBe('#000000');

    // Index 196 = RGB(5,0,0) = bright red in the cube
    expect($palette->indexToHex(196))->toBe('#ff0000');

    // Index 21 = RGB(0,0,5) = bright blue
    expect($palette->indexToHex(21))->toBe('#0000ff');
});

it('converts 256-color grayscale', function () {
    $palette = new AnsiPalette(AnsiPalette::DEFAULTS, '#fff', '#000');

    // Index 232 = darkest gray (8)
    expect($palette->indexToHex(232))->toBe('#080808');

    // Index 255 = lightest gray (238)
    expect($palette->indexToHex(255))->toBe('#eeeeee');
});

it('dims colors by adding alpha', function () {
    expect(AnsiPalette::dimColor('#ff0000'))->toBe('#ff000080');
    expect(AnsiPalette::dimColor('#f00'))->toBe('#ff000080');
});

it('dims colors with existing alpha', function () {
    // #ff0000ff -> alpha 255 / 2 = 127 = 0x7f
    expect(AnsiPalette::dimColor('#ff0000ff'))->toBe('#ff00007f');
});
