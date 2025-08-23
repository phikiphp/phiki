<?php

namespace Phiki;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Extensions\DefaultExtension;
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

    public function addExtension(ExtensionInterface $extension): static
    {
        $extension->register($this);

        return $this;
    }

    public function resolveGrammar(string|Grammar|ParsedGrammar $grammar): ParsedGrammar
    {
        return match (true) {
            is_string($grammar) => $this->grammars->get($grammar),
            $grammar instanceof Grammar => $grammar->toParsedGrammar($this->grammars),
            $grammar instanceof ParsedGrammar => $grammar,
        };
    }

    public function resolveTheme(string|Theme|ParsedTheme $theme): ParsedTheme
    {
        return match (true) {
            is_string($theme) => $this->themes->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->themes),
            $theme instanceof ParsedTheme => $theme,
        };
    }
}
