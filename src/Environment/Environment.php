<?php

namespace Phiki\Environment;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Contracts\TransformerInterface;
use Phiki\Grammar\GrammarRepository;
use Phiki\Theme\ThemeRepository;

class Environment
{
    protected GrammarRepositoryInterface $grammarRepository = new GrammarRepository;

    protected ThemeRepositoryInterface $themeRepository = new ThemeRepository;

    /**
     * @var TransformerInterface[]
     */
    protected array $transformers = [];

    protected bool $strictMode = false;

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

    public function getThemeRepository(): ThemeRepositoryInterface
    {
        return $this->themeRepository;
    }
}