<?php

namespace Phiki;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Contracts\RegexEngineInterface;
use Phiki\Grammar\GrammarRepository;
use Phiki\Grammar\ParsedGrammar;
use Phiki\TextMate\Engines\OnigurumaEngine;
use Phiki\TextMate\Engines\PcreEngine;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\ThemeRepository;
use Psr\SimpleCache\CacheInterface;

class Environment
{
    public readonly GrammarRepository $grammars;

    public readonly ThemeRepository $themes;

    public ?CacheInterface $cache = null;

    public RegexEngineInterface $engine;

    public function __construct()
    {
        $this->grammars = new GrammarRepository;
        $this->themes = new ThemeRepository;

        // Oniguruma is the default throughout 2.x. PCRE is used when PHP has no mbregex,
        // as on PHP 9 or builds without the mb_onig extension.
        $this->engine(OnigurumaEngine::isSupported() ? new OnigurumaEngine : new PcreEngine);
    }

    public function extend(ExtensionInterface $extension): static
    {
        $extension->register($this);

        return $this;
    }

    public function grammar(string $slug, string|ParsedGrammar $grammar): static
    {
        $this->grammars->register($slug, $grammar);

        return $this;
    }

    public function theme(string $slug, string|ParsedTheme $theme): static
    {
        $this->themes->register($slug, $theme);

        return $this;
    }

    public function cache(CacheInterface $cache): static
    {
        $this->cache = $cache;

        return $this;
    }

    public function engine(RegexEngineInterface $engine): static
    {
        $this->engine = $engine;
        $this->grammars->engine($engine->identifier());

        return $this;
    }
}
