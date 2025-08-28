<?php

namespace Phiki\Transformers\Concerns;

trait RequiresThemes
{
    /**
     * @var array<string, \Phiki\Theme\ParsedTheme>
     */
    protected array $themes;

    /**
     * @param array<string, \Phiki\Theme\ParsedTheme> $themes
     */
    public function withThemes(array $themes): void
    {
        $this->themes = $themes;
    }
}
