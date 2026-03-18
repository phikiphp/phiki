<?php

namespace Phiki;

use Phiki\Ansi\AnsiPalette;
use Phiki\Ansi\AnsiToken;
use Phiki\Ansi\AnsiTokenizer;
use Phiki\Contracts\ExtensionInterface;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Highlighting\Highlighter;
use Phiki\Output\Html\PendingHtmlOutput;
use Phiki\Support\Arr;
use Phiki\TextMate\Tokenizer;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;
use Phiki\Theme\TokenSettings;
use Psr\SimpleCache\CacheInterface;

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
        $themes = $this->wrapThemes($theme);

        if ($this->isAnsiGrammar($grammar)) {
            return $this->ansiCodeToHtml($code, $themes);
        }

        return (new PendingHtmlOutput($code, $this->environment->grammars->resolve($grammar), $themes))
            ->cache($this->environment->cache)
            ->generateTokensUsing(fn (string $code, ParsedGrammar $grammar) => $this->codeToTokens($code, $grammar))
            ->highlightTokensUsing(fn (array $tokens, array $themes) => $this->tokensToHighlightedTokens($tokens, $themes));
    }

    protected function isAnsiGrammar(string|Grammar|ParsedGrammar $grammar): bool
    {
        if ($grammar instanceof ParsedGrammar) {
            return $grammar->scopeName === 'text.ansi';
        }

        if ($grammar instanceof Grammar) {
            return $grammar === Grammar::Ansi;
        }

        return $grammar === 'ansi' || $grammar === 'terminal';
    }

    protected function ansiCodeToHtml(string $code, array $themes): PendingHtmlOutput
    {
        $firstTheme = Arr::first($themes);
        $palette = AnsiPalette::fromTheme($firstTheme);

        // Create a stub ParsedGrammar for ANSI
        $ansiGrammar = ParsedGrammar::fromArray([
            'scopeName' => 'text.ansi',
            'name' => 'ansi',
            'patterns' => [],
        ]);

        return (new PendingHtmlOutput($code, $ansiGrammar, $themes))
            ->cache($this->environment->cache)
            ->generateTokensUsing(fn (string $code, ParsedGrammar $grammar) => (new AnsiTokenizer($palette))->tokenize($code))
            ->highlightTokensUsing(fn (array $tokens, array $themes) => $this->ansiTokensToHighlightedTokens($tokens, $themes));
    }

    protected function ansiTokensToHighlightedTokens(array $tokens, array $themes): array
    {
        $highlighter = new Highlighter($themes);

        return $highlighter->highlight($tokens);
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

    public function alias(string $alias, string|Grammar $for): static
    {
        $this->environment->grammars->alias($alias, $for instanceof Grammar ? $for->value : $for);

        return $this;
    }

    public function theme(string $name, string|ParsedTheme $pathOrTheme): static
    {
        $this->environment->themes->register($name, $pathOrTheme);

        return $this;
    }

    public function cache(CacheInterface $cache): static
    {
        $this->environment->cache($cache);

        return $this;
    }
}
