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
        // FIXME: Implement new highlighting logic.
        return $tokens;
    }
}
