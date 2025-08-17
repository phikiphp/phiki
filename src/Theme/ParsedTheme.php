<?php

namespace Phiki\Theme;

class ParsedTheme
{
    /**
     * @param array<string, string> $colors
     * @param TokenColor[] $tokenColors
     */
    public function __construct(
        public string $name,
        public array $colors = [],
        public array $tokenColors = [],
    ) {}

    public function match(array $scopes): ?TokenSettings
    {
        $matches = [];

        foreach ($this->tokenColors as $tokenColor) {
            if ($result = $tokenColor->match($scopes)) {
                $matches[] = new TokenColorMatchResult($tokenColor, $result);
            }
        }

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
        // 3. If there's a tie, figure out how many ancestral matches there were and prefer the one with more ancestors.
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

    public function base(): TokenSettings
    {
        return new TokenSettings(
            $this->colors['editor.background'] ?? null,
            $this->colors['editor.foreground'] ?? null,
            null,
        );
    }
}
