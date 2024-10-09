<?php

namespace Phiki\Environment;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Contracts\TransformerInterface;
use Phiki\Exceptions\EnvironmentException;
use Phiki\Extensions\DefaultExtension;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;
use Phiki\Transformers\ProxyTransformer;

class Environment
{
    protected GrammarRepositoryInterface $grammarRepository;

    protected ThemeRepositoryInterface $themeRepository;

    /**
     * @var TransformerInterface[]
     */
    protected array $transformers = [];

    protected ?ProxyTransformer $proxyTransformer = null;

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

    public function addTransformer(TransformerInterface $transformer): static
    {
        $this->transformers[] = $transformer;

        return $this;
    }

    public function addTransformers(array $transformers): static
    {
        foreach ($transformers as $transformer) {
            $this->addTransformer($transformer);
        }

        return $this;
    }

    /**
     * @return TransformerInterface[]
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    public function getProxyTransformer(): ProxyTransformer
    {
        return $this->proxyTransformer ??= new ProxyTransformer($this->transformers);
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

    final public static function default(): static
    {
        return (new static)->addExtension(new DefaultExtension);
    }
}