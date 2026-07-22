<?php

namespace Phiki\Highlighting;

use Phiki\Ansi\AnsiToken;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\TokenSettings;
use Phiki\Token\HighlightedToken;
use Phiki\Token\Token;

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
                if ($token instanceof AnsiToken) {
                    $settings = $this->buildAnsiSettings($token);
                } else {
                    $settings = $this->matchScopes($token);
                }

                $highlightedTokens[$i][] = new HighlightedToken($token, $settings);
            }
        }

        return $highlightedTokens;
    }

    /**
     * @return array<string, TokenSettings>
     */
    protected function matchScopes(Token $token): array
    {
        $settings = [];

        foreach ($this->themes as $id => $theme) {
            if ($matched = $theme->match($token->scopes)) {
                $settings[$id] = $matched;
            }
        }

        return $settings;
    }

    /**
     * @return array<string, TokenSettings>
     */
    protected function buildAnsiSettings(AnsiToken $token): array
    {
        $tokenSettings = new TokenSettings(
            $token->background,
            $token->foreground,
            $token->fontStyle,
        );

        // All themes get the same settings for ANSI (colors are pre-resolved)
        $settings = [];

        foreach ($this->themes as $id => $theme) {
            $settings[$id] = $tokenSettings;
        }

        return $settings;
    }
}
