<?php

use Phiki\Grammar\Injections\Composite;
use Phiki\Grammar\Injections\Expression;
use Phiki\Grammar\Injections\Filter;
use Phiki\Grammar\Injections\Group;
use Phiki\Grammar\Injections\Path;
use Phiki\Grammar\Injections\Prefix;
use Phiki\Grammar\Injections\Scope;
use Phiki\Grammar\Injections\Selector;

test('a single scope path correctly matches a single scope', function () {
    $path = new Path([
        new Scope(['text', 'html', 'blade', 'php']),
    ]);

    expect($path->matches(['text.html.blade.php']))->toBeTrue();
});

test('a multi scope path correctly matches a single scope', function () {
    $path = new Path([
        new Scope(['text', 'html', 'blade', 'php']),
        new Scope(['meta', 'tag']),
    ]);

    expect($path->matches(['text.html.blade.php']))->toBeFalse();
    expect($path->matches(['text.html.blade.php', 'meta.tag']))->toBeTrue();
});

test('a group correctly matches a set of scopes', function () {
    $group = new Group(new Selector([
        new Composite([new Expression(new Path([
            new Scope(['text', 'html', 'blade', 'php']),
        ]))]),
    ]));

    expect($group->matches(['text.html.blade.php']))->toBeTrue();
});

test('a negated expression correctly matches a set of scopes', function () {
    $expression = new Expression(new Path([
        new Scope(['text', 'html', 'blade', 'php']),
    ]), negated: true);

    expect($expression->matches(['text.html.blade.php']))->toBeFalse();
});

test('a filter correctly matches a set of scopes', function () {
    $filter = new Expression(new Filter(new Path([
        new Scope(['text', 'html', 'blade', 'php']),
    ]), Prefix::Left));

    expect($filter->matches(['text.html.blade.php']))->toBeTrue();
});

test('a scope with wildcards can match another scope', function () {
    $scope = new Scope(['text', 'html', '*', 'php']);

    expect($scope->matches(Scope::fromString('text.html.blade.php')))->toBeTrue();
    expect($scope->matches(Scope::fromString('text.html.twig.php')))->toBeTrue();
});

test('a scope with less parts than comparison does not match', function () {
    $scope = new Scope(['text', 'html', 'blade', 'php']);

    expect($scope->matches(Scope::fromString('text.html.blade')))->toBeFalse();
});
