<?php

namespace Phiki\Environment;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Exceptions\EnvironmentException;
use Phiki\Extensions\DefaultExtension;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;

class Environment
{
    protected GrammarRepositoryInterface $grammarRepository;

    protected ThemeRepositoryInterface $themeRepository;

    public function addExtension(ExtensionInterface $extension): static
    {
        $extension->register($this);

        return $this;
    }

    public function useGrammarRepository(GrammarRepositoryInterface $grammarRepository): static
    {
        $this->grammarRepository = $grammarRepository;

        return $this;
    }

    public function useThemeRepository(ThemeRepositoryInterface $themeRepository): static
    {
        $this->themeRepository = $themeRepository;

        return $this;
    }

    public function getGrammarRepository(): GrammarRepositoryInterface
    {
        return $this->grammarRepository;
    }

    public function resolveGrammar(string|Grammar|ParsedGrammar $grammar): ParsedGrammar
    {
        return match (true) {
            is_string($grammar) => $this->grammarRepository->get($grammar),
            $grammar instanceof Grammar => $grammar->toParsedGrammar($this->grammarRepository),
            $grammar instanceof ParsedGrammar => $grammar,
        };
    }

    public function getThemeRepository(): ThemeRepositoryInterface
    {
        return $this->themeRepository;
    }

    public function resolveTheme(string|Theme|ParsedTheme $theme): ParsedTheme
    {
        return match (true) {
            is_string($theme) => $this->themeRepository->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->themeRepository),
            $theme instanceof ParsedTheme => $theme,
        };
    }

    public function validate(): void
    {
        if (! isset($this->grammarRepository)) {
            throw EnvironmentException::missingGrammarRepository();
        }

        if (! isset($this->themeRepository)) {
            throw EnvironmentException::missingThemeRepository();
        }
    }

    final public static function default(): self
    {
        return (new self)->addExtension(new DefaultExtension);
    }
}
