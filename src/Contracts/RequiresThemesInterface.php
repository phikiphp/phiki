<?php

namespace Phiki\Contracts;

use Phiki\Theme\ParsedTheme;

interface RequiresThemesInterface
{
    /**
     * Set the parsed themes for the transformer.
     * 
     * @param array<string, ParsedTheme> $themes
     */
    public function withThemes(array $themes): void;
}
