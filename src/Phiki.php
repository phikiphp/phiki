<?php

namespace Phiki;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Environment;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Highlighting\Highlighter;
use Phiki\Output\Html\PendingHtmlOutput;
use Phiki\Support\Arr;
use Phiki\TextMate\Tokenizer;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;

class Phiki
{
    public readonly Environment $environment;

    public function __construct()
    {
        $this->environment = new Environment;
    }

    public function environment(): Environment
    {
        return $this->environment;
    }

    public function codeToTokens(string $code, string|Grammar|ParsedGrammar $grammar): array
    {
        $grammar = $this->environment->grammars->resolve($grammar);
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
        return (new PendingHtmlOutput($code, $this->environment->grammars->resolve($grammar), $this->wrapThemes($theme)))
            ->generateTokensUsing(fn (string $code, ParsedGrammar $grammar) => $this->codeToTokens($code, $grammar))
            ->highlightTokensUsing(fn (array $tokens, array $themes) => $this->tokensToHighlightedTokens($tokens, $themes));
    }

    protected function wrapThemes(string|array|Theme $themes): array
    {
        if (! is_array($themes)) {
            $themes = ['default' => $themes];
        }

        return Arr::map($themes, fn (string|Theme|ParsedTheme $theme): ParsedTheme => $this->environment->themes->resolve($theme));
    }

    public function extend(ExtensionInterface $extension): static
    {
        $this->environment->extend($extension);

        return $this;
    }

    public function grammar(string $name, string|ParsedGrammar $pathOrGrammar): static
    {
        $this->environment->grammars->register($name, $pathOrGrammar);

        return $this;
    }
    
    public function theme(string $name, string|ParsedTheme $pathOrTheme): static
    {
        $this->environment->themes->register($name, $pathOrTheme);

        return $this;
    }
}
