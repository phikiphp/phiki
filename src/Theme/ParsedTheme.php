<?php

namespace Phiki\Theme;

class ParsedTheme
{
    /**
     * The token colors the selector index was built from, so it's rebuilt if they change.
     *
     * @var TokenColor[]|null
     */
    protected ?array $indexedTokenColors = null;

    /**
     * The token colors in order, which the index refers to by position.
     *
     * @var list<TokenColor>
     */
    protected array $orderedTokenColors = [];

    /**
     * The selectors of each token color, keyed by the token scope their last scope name matches exactly
     * (`exact`) or as a prefix ending before a dot (`prefix`). Each is a list of [token color, selector].
     *
     * @var array{ exact: array<string, list<array{ 0: int, 1: int }>>, prefix: array<string, list<array{ 0: int, 1: int }>> }
     */
    protected array $index = ['exact' => [], 'prefix' => []];

    /**
     * The selectors whose last scope name matches each token scope, found with the index.
     *
     * @var array<string, list<array{ 0: int, 1: int }>>
     */
    protected array $candidates = [];

    /**
     * @param  array<string, string>  $colors
     * @param  TokenColor[]  $tokenColors
     */
    public function __construct(
        public string $name,
        public array $colors = [],
        public array $tokenColors = [],
    ) {}

    public function match(array $scopes): ?TokenSettings
    {
        $matches = array_is_list($scopes) ? $this->matchIndexed($scopes) : $this->matchEach($scopes);

        // No matches, so no need to highlight.
        if ($matches === []) {
            return null;
        }

        // We've only got a single match so no need to do any specificity calculations.
        if (count($matches) === 1) {
            return $matches[0]->tokenColor->settings;
        }

        // We need to sort the matches based on specificity.
        // The precedence logic based on `vscode-textmate` is:
        // 1. The depth of the match in the token's scope hierarchy -> deeper matches are more specific.
        // 2. The dot count of the matching scope selector -> more segments makes it more specific.
        // 3. If there's a tie, figure out how many ancestral matches there were and prefer the one with more ancestors.
        usort($matches, function (TokenColorMatchResult $a, TokenColorMatchResult $b): int {
            if ($a->scopeMatchResult->depth !== $b->scopeMatchResult->depth) {
                return $b->scopeMatchResult->depth - $a->scopeMatchResult->depth;
            }

            if ($a->scopeMatchResult->length !== $b->scopeMatchResult->length) {
                return $b->scopeMatchResult->length - $a->scopeMatchResult->length;
            }

            return $b->scopeMatchResult->ancestral - $a->scopeMatchResult->ancestral;
        });

        return TokenSettings::flatten(array_map(fn (TokenColorMatchResult $match) => $match->tokenColor->settings, $matches));
    }

    /**
     * Match every token color against the scopes, in order.
     *
     * @return list<TokenColorMatchResult>
     */
    protected function matchEach(array $scopes): array
    {
        $matches = [];

        foreach ($this->tokenColors as $tokenColor) {
            if ($result = $tokenColor->match($scopes)) {
                $matches[] = new TokenColorMatchResult($tokenColor, $result);
            }
        }

        return $matches;
    }

    /**
     * Give the same matches as matchEach(), but only check the selectors whose last scope name matches
     * one of the scopes, which the index finds without comparing the scopes with every selector.
     *
     * @param  list<string>  $scopes
     * @return list<TokenColorMatchResult>
     */
    protected function matchIndexed(array $scopes): array
    {
        if ($this->indexedTokenColors !== $this->tokenColors) {
            $this->buildIndex();
        }

        // The deepest scope each selector's last scope name matches, by token color and selector.
        $depths = [];

        for ($depth = count($scopes) - 1; $depth >= 0; $depth--) {
            foreach ($this->candidates[$scopes[$depth]] ??= $this->findCandidates($scopes[$depth]) as [$i, $j]) {
                $depths[$i][$j] ??= $depth;
            }
        }

        // The matches have to stay in the order of the token colors, which the sort keeps for ties.
        ksort($depths);
        $matches = [];

        foreach ($depths as $i => $selectors) {
            $tokenColor = $this->orderedTokenColors[$i];

            // Like TokenColor::match(), the first of the token color's selectors that matches wins.
            foreach ($tokenColor->scope as $j => $scope) {
                if (! isset($selectors[$j])) {
                    continue;
                }

                $depth = $selectors[$j];
                $length = substr_count($scope->names[array_key_last($scope->names)], '.');

                if (count($scope->names) === 1) {
                    $matches[] = new TokenColorMatchResult($tokenColor, new ScopeMatchResult(length: $length, depth: $depth));

                    break;
                }

                // Like Scope::matches(), only the deepest scope matching the last scope name is tried.
                if ($scope->matchesAncestors(array_reverse(array_slice($scopes, 0, $depth)))) {
                    $matches[] = new TokenColorMatchResult($tokenColor, new ScopeMatchResult(length: $length, depth: $depth, ancestral: count($scope->names) - 1));

                    break;
                }
            }
        }

        return $matches;
    }

    /**
     * Find the selectors whose last scope name matches the scope, as Str::startsWithScope() decides:
     * the scope is the name, or starts with the name, without leading or trailing dots, and a dot.
     *
     * @return list<array{ 0: int, 1: int }>
     */
    protected function findCandidates(string $scope): array
    {
        $candidates = $this->index['exact'][$scope] ?? [];

        for ($dot = strpos($scope, '.'); $dot !== false; $dot = strpos($scope, '.', $dot + 1)) {
            foreach ($this->index['prefix'][substr($scope, 0, $dot)] ?? [] as $candidate) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    protected function buildIndex(): void
    {
        $this->index = ['exact' => [], 'prefix' => []];
        $this->candidates = [];

        $this->orderedTokenColors = array_values($this->tokenColors);

        foreach ($this->orderedTokenColors as $i => $tokenColor) {
            foreach ($tokenColor->scope as $j => $scope) {
                // A selector without scope names never matches.
                if ($scope->names === []) {
                    continue;
                }

                $name = $scope->names[array_key_last($scope->names)];
                $this->index['exact'][$name][] = [$i, $j];
                $this->index['prefix'][trim($name, '.')][] = [$i, $j];
            }
        }

        $this->indexedTokenColors = $this->tokenColors;
    }

    public function base(): TokenSettings
    {
        return new TokenSettings(
            $this->colors['editor.background'] ?? null,
            $this->colors['editor.foreground'] ?? null,
            null,
        );
    }
}
