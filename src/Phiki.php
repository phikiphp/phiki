<?php

namespace Phiki;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Generators\HtmlGenerator;
use Phiki\Generators\TerminalGenerator;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\GrammarRepository;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Theme\ParsedTheme;
use Phiki\Theme\Theme;
use Phiki\Theme\ThemeRepository;

class Phiki
{
    public function __construct(
        protected GrammarRepositoryInterface $grammarRepository = new GrammarRepository,
        protected ThemeRepositoryInterface $themeRepository = new ThemeRepository,
        protected bool $strictMode = false,
    ) {}

    public function codeToTokens(string $code, string|Grammar|ParsedGrammar $grammar): array
    {
        $grammar = match (true) {
            is_string($grammar) => $this->grammarRepository->get($grammar),
            $grammar instanceof Grammar => $grammar->toParsedGrammar($this->grammarRepository),
            default => $grammar,
        };

        $tokenizer = new Tokenizer($grammar, $this->grammarRepository, $this->strictMode);

        return $tokenizer->tokenize($code);
    }

    public function codeToTerminal(string $code, string|Grammar|ParsedGrammar $grammar, string|Theme|ParsedTheme $theme): string
    {
        $tokens = $this->codeToTokens($code, $grammar);

        $theme = match (true) {
            is_string($theme) => $this->themeRepository->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->themeRepository),
            default => $theme,
        };

        $highlighter = new Highlighter($theme);
        $terminalGenerator = new TerminalGenerator($theme);

        return $terminalGenerator->generate($highlighter->highlight($tokens));
    }

    public function codeToHtml(string $code, string|Grammar|ParsedGrammar $grammar, string|Theme|ParsedTheme $theme): string
    {
        $tokens = $this->codeToTokens($code, $grammar);

        $theme = match (true) {
            is_string($theme) => $this->themeRepository->get($theme),
            $theme instanceof Theme => $theme->toParsedTheme($this->themeRepository),
            default => $theme,
        };

        $highlighter = new Highlighter($theme);
        $htmlGenerator = new HtmlGenerator($theme);

        return $htmlGenerator->generate($highlighter->highlight($tokens));
    }

    public static function default(): self
    {
        return new self(new GrammarRepository, new ThemeRepository);
    }
}
