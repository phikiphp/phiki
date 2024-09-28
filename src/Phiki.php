<?php

namespace Phiki;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Grammar\Grammar;

/**
 * @method static string codeToHtml(string $code, string $grammar, string $theme): string
 */
class Phiki
{
    public function __construct(
        protected GrammarRepositoryInterface $grammarRepository = new GrammarRepository,
        protected ThemeRepositoryInterface $themeRepository = new ThemeRepository,
    ) {}

    public function codeToTokens(string $code, string|Grammar $grammar): array
    {
        $grammar = is_string($grammar) ? $this->grammarRepository->get($grammar) : $grammar;

        $tokenizer = new Tokenizer($grammar, $this->grammarRepository);

        return $tokenizer->tokenize($code);
    }

    public function codeToHtml(string $code, string|Grammar $grammar, string $theme): string
    {
        $tokens = $this->codeToTokens($code, $grammar);
        // dd($tokens);
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
