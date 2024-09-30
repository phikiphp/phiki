<?php

use Phiki\Grammar\Grammar;
use Phiki\Grammar\Injections\Injection;
use Phiki\Grammar\Injections\Path;
use Phiki\Grammar\Injections\Selector;

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
});

function injection(string $selector): Injection
{
    $grammar = Grammar::parse([
        'scopeName' => 'source.test',
        'injections' => [
            $selector => [
                'patterns' => [],
            ]
        ],
        'patterns' => [],
    ]);

    return $grammar->getInjections()[0];
}