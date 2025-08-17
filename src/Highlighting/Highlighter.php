<?php

namespace Phiki\Highlighting;

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
                $settings = [];

                foreach ($this->themes as $id => $theme) {
                    if ($matched = $theme->match($token->scopes)) {
                        $settings[$id] = $matched;
                    }
                }

                $highlightedTokens[$i][] = new HighlightedToken($token, $settings);
            }
        }

        return $highlightedTokens;
    }
}
