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
    }

    /**
     * @param \Phiki\Contracts\TransformerInterface[] $transformers
     */
    public function codeToTokens(string $code, string|Grammar|ParsedGrammar $grammar, array $transformers = []): array
    {
        $proxy = new ProxyTransformer($transformers);
        $code = $proxy->preprocess($code);
        
        $grammar = match (true) {
            is_string($grammar) => $this->environment->getGrammarRepository()->get($grammar),
            $grammar instanceof Grammar => $grammar->toParsedGrammar($this->environment->getGrammarRepository()),
            default => $grammar,
        };

        $tokenizer = new Tokenizer($grammar, $this->environment->getGrammarRepository(), $this->environment->isStrictModeEnabled());
        
        return $tokenizer->tokenize($code);
    }

    /**
     * @param \Phiki\Contracts\TransformerInterface[] $transformers
     */
    public function codeToTerminal(string $code, string|Grammar|ParsedGrammar $grammar, string|Theme|ParsedTheme $theme, array $transformers = []): string
    {
        $tokens = $this->codeToTokens($code, $grammar, $transformers);

        $theme = match (true) {
            is_string($theme) => $this->environment->getThemeRepository()->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->environment->getThemeRepository()),
            default => $theme,
        };

        $terminalGenerator = new TerminalGenerator($theme);
        $tokens = $this->highlightTokens($tokens, $theme, $transformers);

        return $terminalGenerator->generate($tokens);
    }

    /**
     * @param \Phiki\Contracts\TransformerInterface[] $transformers
     */
    public function codeToHtml(string $code, string|Grammar|ParsedGrammar $grammar, string|Theme|ParsedTheme $theme, array $transformers = []): string
    {
        $tokens = $this->codeToTokens($code, $grammar, $transformers);

        $theme = match (true) {
            is_string($theme) => $this->environment->getThemeRepository()->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->environment->getThemeRepository()),
            default => $theme,
        };

        $tokens = $this->highlightTokens($tokens, $theme, $transformers);
        $htmlGenerator = new HtmlGenerator($theme, $transformers);

        return $htmlGenerator->generate($tokens);
    }

    protected function highlightTokens(array $tokens, string|Theme|ParsedTheme $theme, array $transformers = []): array
    {
        $theme = match (true) {
            is_string($theme) => $this->environment->getThemeRepository()->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->environment->getThemeRepository()),
            default => $theme,
        };

        $highlighter = new Highlighter($theme);
        $tokens = $highlighter->highlight($tokens);
        $proxy = new ProxyTransformer($transformers);

        return $proxy->tokens($tokens);
    }
}
