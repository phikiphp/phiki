<?php

namespace Phiki\Generators;

use Phiki\Contracts\OutputGeneratorInterface;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\ThemeStyles;

readonly class HtmlGenerator implements OutputGeneratorInterface
{
    public function __construct(
        protected ParsedTheme $theme,
    ) {}

    public function generate(array $tokens): string
    {
        $html = sprintf(
            '<pre class="phiki %s" style="%s"><code>',
            $this->theme->name,
            $this->theme->base()->toStyleString(),
        );

        foreach ($tokens as $line) {
            $html .= '<span class="line">';

            foreach ($line as $token) {
                $html .= sprintf('<span class="token" style="%s">%s</span>', $token->settings?->toStyleString(), htmlspecialchars($token->token->text));
            }

            $html .= '</span>';
        }

        return $html.'</code></pre>';
    }
}
