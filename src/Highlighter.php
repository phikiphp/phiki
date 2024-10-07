<?php

namespace Phiki;

use Phiki\Theme\ThemeStyles;
use Phiki\Token\HighlightedToken;

readonly class Highlighter
{
    public function __construct(public ThemeStyles $styles) {}

    public function highlight(array $tokens): array
    {
        $highlightedTokens = [];

        foreach ($tokens as $i => $line) {
            foreach ($line as $token) {
                $scopes = array_reverse($token->scopes);
                $settings = null;

                foreach ($scopes as $scope) {
                    $resolved = $this->styles->resolve($scope);

                    if ($resolved !== null) {
                        $settings = $resolved;
                        break;
                    }
                }

                $highlightedTokens[$i][] = new HighlightedToken($token, $settings);
            }
        }

        return $highlightedTokens;
    }
}
