<?php

use Phiki\TextMate\SearchResultCache;

it('knows nothing about a regex it has not seen', function () {
    $cache = new SearchResultCache;
    $cache->useLine("foo bar\n");

    expect($cache->get('bar', 0))->toBeNull();
});

it('reuses a match for searches between the original position and the match', function () {
    $cache = new SearchResultCache;
    $cache->useLine("foo bar\n");
    $cache->put('bar', 1, 4, 'captures');

    expect($cache->get('bar', 1))->toBe([4, 'captures']);
    expect($cache->get('bar', 4))->toBe([4, 'captures']);
    expect($cache->get('bar', 0))->toBeNull();
    expect($cache->get('bar', 5))->toBeNull();
});

it('reuses a failed search for later positions only', function () {
    $cache = new SearchResultCache;
    $cache->useLine("foo bar\n");
    $cache->put('baz', 2, false);

    expect($cache->get('baz', 2))->toBeFalse();
    expect($cache->get('baz', 6))->toBeFalse();
    expect($cache->get('baz', 1))->toBeNull();
});

it('does not cache regexes anchored to the search position', function () {
    $cache = new SearchResultCache;
    $cache->useLine("foo bar\n");
    $cache->put('\\Gbar', 4, 4);

    expect($cache->get('\\Gbar', 4))->toBeNull();
});

it('forgets results when the line changes', function () {
    $cache = new SearchResultCache;

    expect($cache->useLine("foo bar\n"))->toBeTrue();

    $cache->put('bar', 0, 4);

    expect($cache->useLine("foo bar\n"))->toBeFalse();
    expect($cache->get('bar', 0))->toBe([4, null]);
    expect($cache->useLine("bar\n"))->toBeTrue();
    expect($cache->get('bar', 0))->toBeNull();
});
