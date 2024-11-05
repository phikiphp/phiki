<?php

namespace Phiki;

use Phiki\Theme\ParsedTheme;
use Phiki\Token\HighlightedToken;

readonly class Highlighter
{
    /**
     * @param  array<string, ParsedTheme>  $themes
     */
    public function __construct(
        public array $themes
    ) {}

    public function highlight(array $tokens): array
    {
        $highlightedTokens = [];

        foreach ($tokens as $i => $line) {
            foreach ($line as $token) {
                $scopes = array_reverse($token->scopes);
                $settings = [];

                foreach ($this->themes as $id => $theme) {
                    foreach ($scopes as $scope) {
                        $resolved = $theme->resolve($scope);

                        if ($resolved !== null) {
                            $settings[$id] = $resolved;
                            break;
                        }
                    }
                }

                $highlightedTokens[$i][] = new HighlightedToken($token, $settings);
            }
        }

        return $highlightedTokens;
    }
}
