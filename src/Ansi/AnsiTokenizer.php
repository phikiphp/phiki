<?php

namespace Phiki\Ansi;

class AnsiTokenizer
{
    protected AnsiParser $parser;

    public function __construct(
        protected AnsiPalette $palette,
    ) {
        $this->parser = new AnsiParser;
    }

    /**
     * @return array<array<AnsiToken>>
     */
    public function tokenize(string $code): array
    {
        $lines = preg_split("/\R/u", $code);
        $result = [];

        foreach ($lines as $line) {
            $result[] = $this->tokenizeLine($line);
        }

        return $result;
    }

    /**
     * @return array<AnsiToken>
     */
    protected function tokenizeLine(string $line): array
    {
        $parsedTokens = $this->parser->parse($line);
        $tokens = [];
        $offset = 0;

        foreach ($parsedTokens as $parsed) {
            $text = $parsed->text;
            $length = strlen($text);

            if ($length === 0) {
                continue;
            }

            $tokens[] = $this->createToken($parsed, $offset, $offset + $length);
            $offset += $length;
        }

        // Ensure at least one token per line (for empty lines)
        if (count($tokens) === 0) {
            $tokens[] = new AnsiToken('', 0, 0);
        }

        return $tokens;
    }

    protected function createToken(ParsedAnsiToken $parsed, int $start, int $end): AnsiToken
    {
        $foreground = $this->resolveForeground($parsed);
        $background = $this->resolveBackground($parsed);
        $fontStyle = $this->resolveFontStyle($parsed);

        // Handle reverse decoration
        if ($parsed->hasDecoration('reverse')) {
            $temp = $foreground ?? $this->palette->defaultForeground;
            $foreground = $background ?? $this->palette->defaultBackground;
            $background = $temp;
        }

        // Handle dim decoration
        if ($parsed->hasDecoration('dim') && $foreground !== null) {
            $foreground = AnsiPalette::dimColor($foreground);
        }

        return new AnsiToken(
            $parsed->text,
            $start,
            $end,
            $foreground,
            $background,
            $fontStyle,
        );
    }

    protected function resolveForeground(ParsedAnsiToken $parsed): ?string
    {
        if ($parsed->foreground === null) {
            return null;
        }

        return $parsed->foreground->resolve($this->palette);
    }

    protected function resolveBackground(ParsedAnsiToken $parsed): ?string
    {
        if ($parsed->background === null) {
            return null;
        }

        return $parsed->background->resolve($this->palette);
    }

    protected function resolveFontStyle(ParsedAnsiToken $parsed): ?string
    {
        $styles = [];

        if ($parsed->hasDecoration('bold')) {
            $styles[] = 'bold';
        }

        if ($parsed->hasDecoration('italic')) {
            $styles[] = 'italic';
        }

        if ($parsed->hasDecoration('underline')) {
            $styles[] = 'underline';
        }

        if ($parsed->hasDecoration('strikethrough')) {
            $styles[] = 'strikethrough';
        }

        return count($styles) > 0 ? implode(' ', $styles) : null;
    }
}
