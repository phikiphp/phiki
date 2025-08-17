<?php

use Phiki\Theme\Scope;
use Phiki\Theme\ScopeMatchResult;

it('returns false if the last scope does not start with the last scope of the object', function () {
    $scope = new Scope([
        'text.html.blade.php',
        'meta.tag',
    ]);

    expect($scope->matches(['text.html.blade.php', 'meta.directive.blade.php']))->toBeFalse();
});

it('returns true if the scope is a single selector and any of the token scopes match', function () {
    $scope = new Scope(['meta.tag']);

    expect($scope->matches(['text.html.blade.php', 'meta.tag']))->not->toBeFalse();
    expect($scope->matches(['text.html.blade.php', 'meta.tag.if.blade.php', 'source.php.if.content.blade.php']))->not->toBeFalse();
});

it('returns the specificity of the matching scope', function () {
    $scope = new Scope(['meta.tag']);

    $result = $scope->matches(['text.html.blade.php', 'meta.tag']);
    expect($result)
        ->toBeInstanceOf(ScopeMatchResult::class)
        ->length->toBe(1) // 'meta.tag' only has 1 dot
        ->depth->toBe(1); // 'meta.tag' has 8 characters
});

it('returns true if a scope selector with two names matches', function () {
    $scope = new Scope(['text.html.blade', 'meta.tag']);

    $result = $scope->matches(['text.html.blade.php', 'meta.directive.blade.php', 'meta.tag']);

    expect($result)
        ->toBeInstanceOf(ScopeMatchResult::class)
        ->length->toBe(1) // 'meta.tag' only contains 1 dot
        ->depth->toBe(2) // 'meta.tag' is the third scope in the token (0 based index)
        ->ancestral->toBe(1); // 'text.html.blade' is a single ancestor scope in the selector
});

it('returns false if an ancestral scope selector does not match', function () {
    $scope = new Scope(['text.html.blade', 'meta.directive']);

    expect($scope->matches(['text.html.blade.php', 'meta.tag']))->toBeFalse();
    expect($scope->matches(['meta.tag', 'text.html.blade.php']))->toBeFalse();
});

it('returns true if a complex scope selector matches', function () {
    $scope = new Scope(['text.html.blade', 'meta.directive', 'meta.tag']);

    $result = $scope->matches(['text.html.blade.php', 'meta.directive.blade.php', 'meta.tag']);

    expect($result)
        ->toBeInstanceOf(ScopeMatchResult::class)
        ->length->toBe(1) // 'meta.tag' only contains 1 dot
        ->depth->toBe(2) // 'meta.tag' is the third scope in the token (0 based index)
        ->ancestral->toBe(2); // 'text.html.blade' and 'meta.directive' are both ancestors in the selector
});
