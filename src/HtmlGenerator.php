<?php

namespace Phiki;

readonly class HtmlGenerator
{
    public function __construct(
        protected ThemeStyles $styles,
    ) {}

    /**
     * @param array<HighlightedToken> $highlightedTokens
     */
    public function generate(array $highlightedTokens): string
    {
        $html = sprintf(
            '<pre class="phiki %s" style="%s"><code>',
            $this->styles->name,
            $this->styles->baseTokenSettings()->toStyleString(),
        );

        foreach ($highlightedTokens as $line) {
            $html .= '<span class="line">';

            foreach ($line as $token) {
                $html .= sprintf('<span class="token" style="%s">%s</span>', $token->settings?->toStyleString(), htmlspecialchars($token->token->text));
            }

            $html .= "</span>";
        }

        return $html . '</code></pre>';
    }
}
