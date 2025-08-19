<?php

namespace Phiki\Generators;

use Closure;
use Stringable;
use Phiki\Token\Token;
use Phiki\Token\HighlightedToken;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Theme\ParsedTheme;

class PendingHtmlOutput implements Stringable
{
    protected bool $withGutter = false;

    protected ?Closure $generateTokensUsing = null;

    protected ?Closure $highlightTokensUsing = null;

    /**
     * @param array<string, ParsedTheme> $themes
     */
    public function __construct(
        protected string $code,
        protected ParsedGrammar $grammar,
        protected array $themes,
    ) {}

    /**
     * @param Closure(string $code, ParsedGrammar $grammar): array<int, array<Token>> $callback
     */
    public function generateTokensUsing(Closure $callback): self
    {
        $this->generateTokensUsing = $callback;

        return $this;
    }

    /**
     * @param Closure(array<int, array<Token>> $tokens, array<string, ParsedTheme> $theme): array<int, array<HighlightedToken>> $callback
     */
    public function highlightTokensUsing(Closure $callback): self
    {
        $this->highlightTokensUsing = $callback;

        return $this;
    }

    public function withGutter(bool $withGutter = true): self
    {
        $this->withGutter = $withGutter;

        return $this;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return (new HtmlGenerator($this->grammar->name, $this->themes, $this->withGutter))
            ->generate(call_user_func(
                $this->highlightTokensUsing,
                call_user_func($this->generateTokensUsing, $this->code, $this->grammar),
                $this->themes,
            ))
            ->__toString();
    }
}
