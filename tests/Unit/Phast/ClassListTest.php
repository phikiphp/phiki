<?php

use Phiki\Phast\ClassList;

it('can add a class', function () {
    $classes = new ClassList();
    $classes->add('foo');

    expect($classes->contains('foo'))->toBeTrue();
});

it('can remove a class', function () {
    $classes = new ClassList(['foo']);
    $classes->remove('foo');

    expect($classes->contains('foo'))->toBeFalse();
});

it('can toggle a class on', function () {
    $classes = new ClassList();
    $classes->toggle('foo', true);

    expect($classes->contains('foo'))->toBeTrue();
});

it('can toggle a class off', function () {
    $classes = new ClassList(['foo']);
    $classes->toggle('foo', false);

    expect($classes->contains('foo'))->toBeFalse();
});

it('can add multiple classes', function () {
    $classes = new ClassList();
    $classes->add('foo', 'bar');

    expect($classes->contains('foo'))->toBeTrue();
    expect($classes->contains('bar'))->toBeTrue();
});

it('can remove multiple classes', function () {
    $classes = new ClassList(['foo', 'bar']);
    $classes->remove('foo', 'bar');

    expect($classes->contains('foo'))->toBeFalse();
    expect($classes->contains('bar'))->toBeFalse();
});

it('can check if a class exists', function () {
    $classes = new ClassList(['foo', 'bar']);

    expect($classes->contains('foo'))->toBeTrue();
    expect($classes->contains('baz'))->toBeFalse();
});

it('can convert to string', function () {
    $classes = new ClassList(['foo', 'bar']);
    
    expect((string) $classes)->toBe('foo bar');
});
