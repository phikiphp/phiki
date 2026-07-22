<?php

use Phiki\Grammar\Injections\Composite;
use Phiki\Grammar\Injections\Expression;
use Phiki\Grammar\Injections\Filter;
use Phiki\Grammar\Injections\Group;
use Phiki\Grammar\Injections\Operator;
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

describe('composite operators', function () {
    // A path that matches SCOPES, and one that does not, so each expression in a
    // composite can be steered independently.
    $matching = fn () => new Path([new Scope(['text', 'html', 'blade', 'php'])]);
    $notMatching = fn () => new Path([new Scope(['nope'])]);

    $composite = function (array $parts) use ($matching, $notMatching) {
        return new Composite(array_map(
            fn (array $part) => new Expression($part[1] ? $matching() : $notMatching(), $part[0]),
            $parts,
        ));
    };

    it('combines expressions according to the operator', function (array $parts, bool $expected) use ($composite) {
        expect($composite($parts)->matches(['text.html.blade.php']))->toBe($expected);
    })->with([
        'single truthy' => [[[Operator::None, true]], true],
        'single falsy' => [[[Operator::None, false]], false],

        'true and true' => [[[Operator::None, true], [Operator::And, true]], true],
        'true and false' => [[[Operator::None, true], [Operator::And, false]], false],
        'false and true' => [[[Operator::None, false], [Operator::And, true]], false],
        'false and false' => [[[Operator::None, false], [Operator::And, false]], false],

        'true or true' => [[[Operator::None, true], [Operator::Or, true]], true],
        'true or false' => [[[Operator::None, true], [Operator::Or, false]], true],
        'false or true' => [[[Operator::None, false], [Operator::Or, true]], true],
        'false or false' => [[[Operator::None, false], [Operator::Or, false]], false],

        'true not true' => [[[Operator::None, true], [Operator::Not, true]], false],
        'true not false' => [[[Operator::None, true], [Operator::Not, false]], true],
        'false not true' => [[[Operator::None, false], [Operator::Not, true]], false],
        'false not false' => [[[Operator::None, false], [Operator::Not, false]], false],

        'chained and/or' => [[[Operator::None, false], [Operator::Or, true], [Operator::And, true]], true],
        'chained and/not' => [[[Operator::None, true], [Operator::And, true], [Operator::Not, true]], false],
        'or recovers from a failed and' => [[[Operator::None, true], [Operator::And, false], [Operator::Or, true]], true],
    ]);
});
