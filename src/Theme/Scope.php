<?php

namespace Phiki\Theme;

use Phiki\Support\Arr;
use Phiki\Support\Str;

class Scope
{
    public function __construct(
        public array $names,
    ) {}

    /**
     * @param array<string> $scopes
     */
    public function matches(array $scopes): ScopeMatchResult | false
    {
        // We want to reverse the token scopes since we will need to check from right to left.
        $reversed = array_reverse($scopes);
        $valuesToKeys = array_flip($scopes);

        // If there's only a single scope in the object, we can just check to see if any of the scopes
        // from the token start with the scope from the object, saving some additional processing.
        if (count($this->names) === 1) {
            foreach ($reversed as $scope) {
                if (! Str::startsWithScope($scope, $this->names[0])) {
                    continue;
                }

                // We need to calculate a specificity for the match so that we can determine which match
                // should be used to highlight the token among a set of other scope matches.
                //
                // My interpretation of TextMate's specificity rules is that it should be based on:
                //    a. the dot count of the matching scope selector
                //    b. how deep it is in the token's scope hierarchy
                // To keep track of that info, we can return a result object that stores it all
                // and then once we've found all matching scopes for a token, we can sort them
                // based on those rules with some precedence logic too.
                return new ScopeMatchResult(length: Str::dotCount($this->names[0]), depth: $valuesToKeys[$scope]);
            }
            
            return false;
        }

        // If we've got multiple scopes in the object then we must be doing a CSS-style selector match.
        // We need to check to see if any of the token scopes match the last part of the scope object.
        //
        // For example, if the token has the following scopes:
        //     ['source.php', 'meta.class.php', 'meta.class.body.php', 'storage.type.function.php']
        // and the scope object has the following scopes:
        //     ['source.php', 'meta.class']
        // then we should get a match because the last scope in the object ('meta.class') matches
        // the `meta.class.body.php` and `meta.class.php` scopes in the token.
        //
        // If we find a match, we need to get rid of everything after that matching scope name
        // and perform a CSS-style selector match from the right to the left.
        //
        // Using CSS as an example:
        // .foo .bar will match any .bar elements inside of .foo, regardless of the depth of .bar.
        //
        // So equally, if we have a scope object with ['source.php', 'meta.class'] and a token with
        // ['source.php', 'meta.class.php', 'meta.class.body.php', 'storage.type.function.php'],
        // then we would match the 'meta.class' scope with the `meta.class.body.php` scope in the token
        // and then we would check to see if any of the previous scopes in the token match `source.php`.
        // This would continue as long as there are still scopes in the object to check against.
        $lastScopeSelector = end($this->names);

        // This is the scope selector that we matched.
        $matchingScope = null;
        // This is the depth in the token's scope hierarchy that the matching scope was found.
        $matchingScopeDepth = 0;
        // This is the length of the matching scope selector.
        $matchingScopeLength = 0;
        // This is the index of the matching scope in the reversed token scopes. 
        // We only use this to chop off the scopes after the matching scope since
        // we don't need to check those anymore.
        $matchingScopeIndex = 0;

        foreach ($reversed as $index => $scope) {
            if (! Str::startsWithScope($scope, $lastScopeSelector)) {
                continue;
            }

            $matchingScope = $scope;
            $matchingScopeDepth = $valuesToKeys[$scope];
            $matchingScopeLength = Str::dotCount($lastScopeSelector);
            $matchingScopeIndex = $index;

            // We've found a matching scope so we can break out of the loop as we don't need to do any more checks.
            break;
        }

        // If we didn't find a matching scope from the token then we can return early.
        if ($matchingScope === null) {
            return false;
        }

        // We can now get rid of any scopes that come after the matching scope,
        // skipping over the one we just matched since we don't need to check that again.
        $ancestralScopesReversed = array_slice($reversed, $matchingScopeIndex + 1);
        $remainingScopeSelectorsReversed = array_reverse(array_slice($this->names, 0, -1));

        // Now we need to check the remaining scopes in the object against the ancestral scopes.
        foreach ($remainingScopeSelectorsReversed as $scopeName) {
            $matchedAncestralScopeIndex = null;

            foreach ($ancestralScopesReversed as $index => $ancestralScope) {
                // If we find a matching ancestral scope that starts with the scope name,
                // we can store the index of it so we can chop off the scopes we don't need to check
                // and then break out of the loop.
                if (Str::startsWithScope($ancestralScope, $scopeName)) {
                    $matchedAncestralScopeIndex = $index;
                    break;
                }
            }

            // We didn't find a matching ancestral scope so it's impossible to match the scope.
            if ($matchedAncestralScopeIndex === null) {
                return false;
            }

            // If we reach this point, we've found a matching ancestral scope.
            // It's possible that we have more scope selectors to check against,
            // so we need to modify the ancestral scopes to only include those
            // that come after the one we just matched.
            $ancestralScopesReversed = array_slice($ancestralScopesReversed, $matchedAncestralScopeIndex);
        }

        // If we've not returned false by this point, it means we've matched all of the scopes in the object
        // against the ancestral scopes of the token and we can therefore return a match result.
        //
        // The specificity of this match is slightly different because we've checked against ancestral scopes,
        // so we need to include that in the result so we can use it to sort matches later.
        return new ScopeMatchResult(length: $matchingScopeLength, depth: $matchingScopeDepth, ancestral: count($this->names) - 1);
    }
}
