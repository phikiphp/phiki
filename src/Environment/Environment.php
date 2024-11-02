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

    protected bool $strictMode = false;

    public function addExtension(ExtensionInterface $extension): static
    {
        $extension->register($this);

        return $this;
    }

    public function enableStrictMode(): static
    {
        $this->strictMode = true;

        return $this;
    }

    public function disableStrictMode(): static
    {
        $this->strictMode = false;

        return $this;
    }

    public function isStrictModeEnabled(): bool
    {
        return $this->strictMode;
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

    public function resolveGrammar(string|Grammar $grammar): ParsedGrammar
    {
        return match (true) {
            is_string($grammar) => $this->grammarRepository->get($grammar),
            $grammar instanceof Grammar => $grammar->toParsedGrammar($this->grammarRepository),
        };
    }

    public function getThemeRepository(): ThemeRepositoryInterface
    {
        return $this->themeRepository;
    }

    public function resolveTheme(string|Theme $theme): ParsedTheme
    {
        return match (true) {
            is_string($theme) => $this->themeRepository->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->themeRepository),
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
