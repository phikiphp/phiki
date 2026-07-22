<?php

use Phiki\Support\Styles;

it('returns an empty string when there is nothing to join', function (array $chunks) {
    expect(Styles::join($chunks))->toBe('');
})->with([
    'no chunks' => [[]],
    'empty chunks' => [['', '']],
    'null chunks' => [[null, null]],
    'separators only' => [[';', ';;']],
]);

it('terminates the joined value with a separator', function () {
    expect(Styles::join(['color: #fff']))->toBe('color: #fff;');
});

it('does not double up separators when a chunk is already terminated', function () {
    expect(Styles::join(['background-color: #fff;color: #24292e;', '--phiki-dark-color: #e1e4e8']))
        ->toBe('background-color: #fff;color: #24292e;--phiki-dark-color: #e1e4e8;');
});

it('skips empty and null chunks rather than emitting empty declarations', function () {
    expect(Styles::join([null, 'color: #fff', '', '-webkit-user-select: none']))
        ->toBe('color: #fff;-webkit-user-select: none;');
});

it('normalises whitespace around declarations', function () {
    expect(Styles::join(['  color: #fff ; background-color: #000 ']))
        ->toBe('color: #fff;background-color: #000;');
});
