<?php

namespace Phiki;

use Phiki\Environment\Environment;
use Phiki\Generators\HtmlGenerator;
use Phiki\Generators\PendingHtmlOutput;
use Phiki\Generators\TerminalGenerator;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Highlighting\Highlighter;
use Phiki\Support\Arr;
use Phiki\Support\Str;
use Phiki\TextMate\Tokenizer;
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

    public function codeToTokens(string $code, string|Grammar|ParsedGrammar $grammar): array
    {
        $grammar = $this->environment->resolveGrammar($grammar);
        $tokenizer = new Tokenizer($grammar, $this->environment);

        return $tokenizer->tokenize($code);
    }

    public function tokensToHighlightedTokens(array $tokens, string|array|Theme $theme): array
    {
        $themes = $this->wrapThemes($theme);
        $highlighter = new Highlighter($themes);

        return $highlighter->highlight($tokens);
    }

    public function codeToHighlightedTokens(string $code, string|Grammar $grammar, string|array|Theme $theme): array
    {
        $tokens = $this->codeToTokens($code, $grammar);
        $themes = $this->wrapThemes($theme);
        $highlighter = new Highlighter($themes);

        return $highlighter->highlight($tokens);
    }

    public function codeToHtml(string $code, string|Grammar $grammar, string|array|Theme $theme): PendingHtmlOutput
    {
        return (new PendingHtmlOutput($code, $this->environment->resolveGrammar($grammar), $this->wrapThemes($theme)))
            ->generateTokensUsing(fn (string $code, ParsedGrammar $grammar) => $this->codeToTokens($code, $grammar))
            ->highlightTokensUsing(fn (array $tokens, array $themes) => $this->tokensToHighlightedTokens($tokens, $themes));
    }

    protected function wrapThemes(string|array|Theme $themes): array
    {
        if (! is_array($themes)) {
            $themes = ['default' => $themes];
        }

        return Arr::map($themes, fn (string|Theme|ParsedTheme $theme): ParsedTheme => $this->environment->resolveTheme($theme));
    }
}
