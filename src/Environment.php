<?php

namespace Phiki;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\GrammarRepository;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;
use Phiki\Theme\ThemeRepository;

class Environment
{
    public readonly GrammarRepository $grammars;

    public readonly ThemeRepository $themes;

    public function __construct()
    {
        $this->grammars = new GrammarRepository;
        $this->themes = new ThemeRepository;
    }

    public function extend(ExtensionInterface $extension): static
    {
        $extension->register($this);

        return $this;
    }
}
