<?php

namespace Phiki\Generators;

use Phiki\Contracts\OutputGeneratorInterface;
use Phiki\Support\Arr;
use Phiki\Theme\ParsedTheme;

class HtmlGenerator implements OutputGeneratorInterface
{
    /**
     * @param  array<string, ParsedTheme>  $themes
     */
    public function __construct(
        protected ?string $grammarName,
        protected array $themes,
        protected bool $withGutter = false,
        protected bool $withWrapper = false,
    ) {}

    public function generate(array $tokens): string
    {
        return $this->withWrapper ? $this->buildWrapper($tokens) : $this->buildPre($tokens);
    }

    private function buildWrapper($tokens): string
    {
        $wrapperStyles = [$this->getDefaultTheme()->base()->toStyleString()];

        foreach ($this->themes as $id => $theme) {
            if ($id !== $this->getDefaultThemeId()) {
                $wrapperStyles[] = $theme->base()->toCssVarString($id);
            }
        }

        return sprintf(
            '<div class="phiki-wrapper"%s style="%s">%s</div>',
            $this->grammarName ? " data-language=\"$this->grammarName\"" : null,
            implode(';', $wrapperStyles),
            $this->buildPre($tokens)
        );
    }

    private function buildPre($tokens): string
    {
        $preClasses = array_filter([
            'phiki',
            $this->grammarName ? "language-$this->grammarName" : null,
            $this->getDefaultTheme()->name,
            count($this->themes) > 1 ? 'phiki-themes' : null,
        ]);

        foreach ($this->themes as $theme) {
            if ($theme !== $this->getDefaultTheme()) {
                $preClasses[] = $theme->name;
            }
        }

        $preStyles = [$this->getDefaultTheme()->base()->toStyleString()];

        foreach ($this->themes as $id => $theme) {
            if ($id !== $this->getDefaultThemeId()) {
                $preStyles[] = $theme->base()->toCssVarString($id);
            }
        }

        return sprintf(
            '<pre class="%s"%s style="%s">%s</pre>',
            implode(' ', $preClasses),
            $this->grammarName ? " data-language=\"$this->grammarName\"" : null,
            implode(';', $preStyles),
            $this->buildCode($tokens)
        );
    }

    private function buildCode(array $tokens): string
    {
        $output = [];

        foreach ($tokens as $i => $line) {
            $output[] = $this->buildLine($line, $i);
        }

        return '<code>'.implode($output).'</code>';
    }

    private function buildLine(array $line, int $index): string
    {
        $output = [];

        if ($this->withGutter) {
            $output[] = $this->buildLineNumber($index + 1);
        }

        foreach ($line as $token) {
            $output[] = $this->buildToken($token);
        }

        return '<span class="line">'.implode($output).'</span>';
    }

    private function buildLineNumber(int $lineNumber): string
    {
        $lineNumberColor = $this->getDefaultTheme()->colors['editorLineNumber.foreground'] ?? null;

        $lineNumberStyles = $lineNumberColor ? "color: $lineNumberColor; " : null;
        $lineNumberStyles .= '-webkit-user-select: none; user-select: none;';

        return sprintf(
            '<span class="line-number" style="%s">%2d</span>',
            $lineNumberStyles,
            $lineNumber
        );
    }

    private function buildToken(object $token): string
    {
        $tokenStyles = [($token->settings[$this->getDefaultThemeId()] ?? null)?->toStyleString()];

        foreach ($token->settings as $id => $settings) {
            if ($id !== $this->getDefaultThemeId()) {
                $tokenStyles[] = $settings->toCssVarString($id);
            }
        }

        $styleString = implode(';', array_filter($tokenStyles));

        return sprintf(
            '<span class="token"%s>%s</span>',
            $styleString ? " style=\"$styleString\"" : null,
            htmlspecialchars($token->token->text)
        );
    }

    private function getDefaultTheme(): ParsedTheme
    {
        return Arr::first($this->themes);
    }

    private function getDefaultThemeId(): string
    {
        return Arr::firstKey($this->themes);
    }
}
