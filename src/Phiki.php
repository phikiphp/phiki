<?php

namespace Phiki;

use Phiki\Environment\Environment;
use Phiki\Generators\HtmlGenerator;
use Phiki\Generators\TerminalGenerator;
use Phiki\Grammar\Grammar;
use Phiki\Support\Arr;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;

class Phiki
{
    protected Environment $environment;

    public function __construct(?Environment $environment = null)
    {
        $this->environment = $environment ?? Environment::default();
        $this->environment->validate();
    }

    public function codeToTokens(string $code, string|Grammar $grammar): array
    {
        $grammar = $this->environment->resolveGrammar($grammar);
        $tokenizer = new Tokenizer($grammar, $this->environment);

        return $tokenizer->tokenize($code);
    }

    public function codeToHighlightedTokens(string $code, string|Grammar $grammar, string|array|Theme $theme): array
    {
        $tokens = $this->codeToTokens($code, $grammar);
        $themes = $this->wrapThemes($theme);
        $highlighter = new Highlighter($themes);

        return $highlighter->highlight($tokens);
    }

    public function codeToTerminal(string $code, string|Grammar $grammar, string|Theme $theme): string
    {
        $tokens = $this->codeToHighlightedTokens($code, $grammar, $theme);
        $generator = new TerminalGenerator($this->environment->resolveTheme($theme));

        return $generator->generate($tokens);
    }

    public function codeToHtml(string $code, string|Grammar $grammar, string|array|Theme $theme): string
    {
        $tokens = $this->codeToHighlightedTokens($code, $grammar, $theme);
        $generator = new HtmlGenerator(
            match (true) {
                is_string($grammar) => $grammar,
                default => $this->environment->resolveGrammar($grammar)->name,
            },
            $this->wrapThemes($theme),
        );

        return $generator->generate($tokens);
    }

    protected function wrapThemes(string|array|Theme $themes): array
    {
        if (! is_array($themes)) {
            $themes = ['default' => $themes];
        }

        return Arr::map($themes, fn (string|Theme $theme): ParsedTheme => $this->environment->resolveTheme($theme));
    }
}
