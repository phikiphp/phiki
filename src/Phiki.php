<?php

namespace Phiki;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\ThemeRepositoryInterface;

/**
 * @method static string codeToHtml(string $code, string $grammar, string $theme): string
 */
class Phiki
{
    public function __construct(
        protected GrammarRepositoryInterface $grammarRepository = new GrammarRepository,
        protected ThemeRepositoryInterface $themeRepository = new ThemeRepository,
    ) {}

    public function codeToHtml(string $code, string $grammar, string $theme): string
    {
        $grammar = $this->grammarRepository->get($grammar);
        $theme = $this->themeRepository->get($theme);

        $tokenizer = new Tokenizer($grammar, $this->grammarRepository);
        $tokens = $tokenizer->tokenize($code);

        $styles = new ThemeStyles($theme);
        $highlighter = new Highlighter($styles);
        $htmlGenerator = new HtmlGenerator($styles);

        return $htmlGenerator->generate($highlighter->highlight($tokens));
    }

    public static function default(): static
    {
        return new static(new GrammarRepository, new ThemeRepository);
    }
}