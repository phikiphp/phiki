<?php

namespace Phiki;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Generators\HtmlGenerator;
use Phiki\Grammar\GrammarRepository;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Theme\ThemeRepository;
use Phiki\Theme\ThemeStyles;

class Phiki
{
    public function __construct(
        protected GrammarRepositoryInterface $grammarRepository = new GrammarRepository,
        protected ThemeRepositoryInterface $themeRepository = new ThemeRepository,
        protected bool $strictMode = false,
    ) {}

    public function codeToTokens(string $code, string|ParsedGrammar $grammar): array
    {
        $grammar = is_string($grammar) ? $this->grammarRepository->get($grammar) : $grammar;

        $tokenizer = new Tokenizer($grammar, $this->grammarRepository, $this->strictMode);

        return $tokenizer->tokenize($code);
    }

    public function codeToHtml(string $code, string|ParsedGrammar $grammar, string $theme): string
    {
        $tokens = $this->codeToTokens($code, $grammar);

        $theme = $this->themeRepository->get($theme);
        $styles = new ThemeStyles($theme);

        $highlighter = new Highlighter($styles);
        $htmlGenerator = new HtmlGenerator($styles);

        return $htmlGenerator->generate($highlighter->highlight($tokens));
    }

    public static function default(): self
    {
        return new self(new GrammarRepository, new ThemeRepository);
    }
}
