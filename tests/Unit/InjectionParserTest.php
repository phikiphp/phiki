<?php

use Phiki\Grammar\Injections\Filter;
use Phiki\Grammar\Injections\Group;
use Phiki\Grammar\Injections\Injection;
use Phiki\Grammar\Injections\Operator;
use Phiki\Grammar\Injections\Path;
use Phiki\Grammar\Injections\Prefix;
use Phiki\Grammar\Injections\Selector;
use Phiki\Grammar\ParsedGrammar;

describe('InjectionParser', function () {
    it('can parse a simple path', function () {
        $injection = injection('text.html.blade.php');

        $selector = $injection->getSelector();

        expect($selector)->toBeInstanceOf(Selector::class);
        expect($selector->composites)->toBeArray();
        expect($selector->composites)->toHaveCount(1);

        $composite = $selector->composites[0];

        expect($composite->expressions)->toBeArray();
        expect($composite->expressions)->toHaveCount(1);

        $expression = $composite->expressions[0];

        expect($expression->child)->toBeInstanceOf(Path::class);
        expect($expression->child->scopes)->toBeArray();
        expect($expression->child->scopes)->toHaveCount(1);

        $scope = $expression->child->scopes[0];

        expect($scope->__toString())->toBe('text.html.blade.php');
    });

    it('can parse a multi-part path', function () {
        $injection = injection('text.html.blade.php meta.tag');

        $scopes = $injection->getSelector()->composites[0]->expressions[0]->child->scopes;

        expect($scopes[0]->__toString())->toBe('text.html.blade.php');
        expect($scopes[1]->__toString())->toBe('meta.tag');
    });

    it('can parse a left-prefixed path', function () {
        $injection = injection('L:text.html.blade.php');

        $filter = $injection->getSelector()->composites[0]->expressions[0]->child;

        expect($filter)->toBeInstanceOf(Filter::class);
        expect($filter->prefix)->toBe(Prefix::Left);

        $path = $filter->child;

        expect($path)->toBeInstanceOf(Path::class);
        expect($path->scopes)->toBeArray();
        expect($path->scopes)->toHaveCount(1);

        $scope = $path->scopes[0];

        expect($scope->__toString())->toBe('text.html.blade.php');
    });

    it('can parse a right-prefixed path', function () {
        $injection = injection('R:text.html.blade.php');

        $filter = $injection->getSelector()->composites[0]->expressions[0]->child;

        expect($filter)->toBeInstanceOf(Filter::class);
        expect($filter->prefix)->toBe(Prefix::Right);

        $path = $filter->child;

        expect($path)->toBeInstanceOf(Path::class);
        expect($path->scopes)->toBeArray();
        expect($path->scopes)->toHaveCount(1);

        $scope = $path->scopes[0];

        expect($scope->__toString())->toBe('text.html.blade.php');
    });

    it('can parse a grouped path', function () {
        $injection = injection('(text.html.blade.php)');

        $group = $injection->getSelector()->composites[0]->expressions[0]->child;

        expect($group)->toBeInstanceOf(Group::class);

        $path = $group->child;

        expect($path)->toBeInstanceOf(Selector::class);

        $scope = $path->composites[0]->expressions[0]->child->scopes[0];

        expect($scope->__toString())->toBe('text.html.blade.php');
    });

    it('can parse a grouped path with a prefix', function () {
        $injection = injection('L:(text.html.blade.php)');

        $filter = $injection->getSelector()->composites[0]->expressions[0]->child;

        expect($filter)->toBeInstanceOf(Filter::class);
        expect($filter->prefix)->toBe(Prefix::Left);

        $group = $filter->child;

        expect($group)->toBeInstanceOf(Group::class);

        $path = $group->child;

        expect($path)->toBeInstanceOf(Selector::class);

        $scope = $path->composites[0]->expressions[0]->child->scopes[0];

        expect($scope->__toString())->toBe('text.html.blade.php');
    });

    it('can parse comma-separated paths', function () {
        $injection = injection('text.html.blade.php, meta.tag');

        $first = $injection->getSelector()->composites[0]->expressions[0]->child->scopes;

        expect($first[0]->__toString())->toBe('text.html.blade.php');

        $second = $injection->getSelector()->composites[1]->expressions[0]->child->scopes;

        expect($second[0]->__toString())->toBe('meta.tag');
    });

    it('can parse a negated path', function () {
        $injection = injection('text.html.php.blade - meta.tag');

        $first = $injection->getSelector()->composites[0]->expressions[0]->child->scopes[0];

        expect($first->__toString())->toBe('text.html.php.blade');

        $second = $injection->getSelector()->composites[0]->expressions[1];

        expect($second->operator)->toBe(Operator::Not);
        expect($second->child->scopes[0]->__toString())->toBe('meta.tag');
    });

    it('can parse a group with or operators', function () {
        $injection = injection('(text.html.blade.php | meta.tag)');

        $group = $injection->getSelector()->composites[0]->expressions[0]->child;

        expect($group)->toBeInstanceOf(Group::class);

        $first = $group->child->composites[0]->expressions[0]->child->scopes[0];

        expect($first->__toString())->toBe('text.html.blade.php');

        $second = $group->child->composites[0]->expressions[1];

        expect($second->operator)->toBe(Operator::Or);
        expect($second->child->scopes[0]->__toString())->toBe('meta.tag');
    });
});

function injection(string $selector): Injection
{
    $grammar = ParsedGrammar::fromArray([
        'scopeName' => 'source.test',
        'injections' => [
            $selector => [
                'patterns' => [],
            ],
        ],
        'patterns' => [],
    ]);

    return $grammar->getInjections()[0];
}
