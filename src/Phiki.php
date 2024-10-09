<?php

namespace Phiki;

use Phiki\Environment\Environment;
use Phiki\Generators\HtmlGenerator;
use Phiki\Generators\TerminalGenerator;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;
use Phiki\Transformers\ProxyTransformer;

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
        $code = $this->environment->getProxyTransformer()->preprocess($code);
        $grammar = $this->environment->resolveGrammar($grammar);
        $tokenizer = new Tokenizer($grammar, $this->environment);
        
        return $tokenizer->tokenize($code);
    }

    public function codeToHighlightedTokens(string $code, string|Grammar $grammar, string|Theme $theme): array
    {
        $tokens = $this->codeToTokens($code, $grammar);
        $theme = $this->environment->resolveTheme($theme);
        $highlighter = new Highlighter($theme);

        return $highlighter->highlight($tokens);
    }

    public function codeToTerminal(string $code, string|Grammar $grammar, string|Theme $theme): string
    {
        $tokens = $this->codeToHighlightedTokens($code, $grammar, $theme);
        $generator = new TerminalGenerator($this->environment->resolveTheme($theme));

        return $generator->generate($tokens);
    }

    public function codeToHtml(string $code, string|Grammar $grammar, string|Theme $theme): string
    {
        $tokens = $this->codeToHighlightedTokens($code, $grammar, $theme);
        $generator = new HtmlGenerator($this->environment->resolveTheme($theme), $this->environment->getProxyTransformer());

        return $generator->generate($tokens);
    }
}
