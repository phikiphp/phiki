<?php

namespace Phiki\Support;

use Stringable;

/**
 * This class is responsible for converting an Oniguruma pattern into a PCRE2/PHP compatible pattern.
 */
class Regex implements Stringable
{
    const SLASH_P_MAP = [
        'number' => '0-9',
        'alnum' => '0-9A-Za-z',
        'alpha' => 'A-Za-z',
        'alphabetic' => 'A-Za-z',
        'blank' => '\\s',
        'greek' => '\\p{Greek}',
        'print' => '\\p{L}\\p{N}\\p{P}\\p{S}\\p{Zs}',
        'word' => '\\w',
    ];

    protected bool $hasAnchor;

    protected array $anchorCache = [];

    protected string $pattern;

    public function __construct(
        string $pattern,
        protected ?string $lowered = null,
    ) {
        $length = strlen($pattern);
        $lastPushedPos = 0;
        $output = [];
        $hasAnchor = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $pattern[$i];

            if ($char === '\\') {
                if ($i + 1 < $length) {
                    $nextChar = $pattern[$i + 1];

                    if ($nextChar === 'z') {
                        $output[] = substr($pattern, $lastPushedPos, $i - $lastPushedPos);
                        $output[] = '$(?!\\n)(?<!\\n)';
                        $lastPushedPos = $i + 2;
                    } elseif ($nextChar === 'A' || $nextChar === 'G') {
                        $hasAnchor = true;
                    }

                    $i++;
                }
            }
        }

        $this->hasAnchor = $hasAnchor;

        if ($lastPushedPos === 0) {
            $this->pattern = $pattern;
        } else {
            $output[] = substr($pattern, $lastPushedPos);

            $this->pattern = implode('', $output);
        }

        $this->pattern = preg_replace('/(?<!\\\)\//', '\\/', $this->pattern);
        $this->pattern = $this->convertEscapeSequences($this->pattern);
        $this->pattern = $this->convertUnicodeProperties($this->pattern);
        $this->pattern = $this->escapeInvalidLeadingRangeCharacter($this->pattern);
        $this->pattern = $this->escapeUnescapedCloseSetCharacters($this->pattern);
        $this->pattern = $this->convertUnsupportedUnicodeEscapes($this->pattern);

        if ($this->hasAnchor) {
            $this->anchorCache = $this->buildAnchorCache();
        }
    }

    public function get(bool $allowA = false, bool $allowG = false): string
    {
        return $this->resolveAnchors($this->pattern, $allowA, $allowG);
    }

    protected function convertEscapeSequences(string $pattern): string
    {
        // Convert \h to [0-9A-Fa-f].
        $pattern = preg_replace('/\\\\h/', '[0-9A-Fa-f]', $pattern);

        // Convert \H to [^0-9A-Fa-f].
        $pattern = preg_replace('/\\\\H/', '[^0-9A-Fa-f]', $pattern);

        // Remove \R as it is not supported in PCRE.
        $pattern = preg_replace('/\\\\R/', '', $pattern);

        // Remove dangling \p without braces.
        $pattern = preg_replace('/\\\\p(?![{])/', '', $pattern);

        return $pattern;
    }

    protected function convertUnicodeProperties(string $pattern): string
    {
        // Convert \p{xx} to PCRE-compatible \p{xx}.
        $pattern = preg_replace_callback('/\\\p\{([a-zA-Z]+)\}/', function (array $matches) {
            $property = strtolower($matches[1]);

            if (isset(self::SLASH_P_MAP[$property])) {
                return '['.self::SLASH_P_MAP[$property].']';
            }

            return $matches[0];
        }, $pattern);

        return $pattern;
    }

    protected function escapeInvalidLeadingRangeCharacter(string $pattern): string
    {
        // Escape invalid leading range function characters, e.g. [-...].
        $pattern = preg_replace('/\[(?<!\\\)(-)/', '[\\-', $pattern);

        return $pattern;
    }

    protected function escapeUnescapedCloseSetCharacters(string $pattern): string
    {
        // Escape unescaped close set characters, e.g. ]] converted to \]].
        $pattern = preg_replace('/(?<!:)(?<!\\\)\]\]/', '\\]]', $pattern);

        return $pattern;
    }

    protected function convertUnsupportedUnicodeEscapes(string $pattern): string
    {
        // Convert \uXXXX to \x{XXXX}.
        $pattern = preg_replace('/\\\\u([0-9A-Fa-f]{4})/', '\\x{$1}', $pattern);

        // Convert 5+ digit \x{AXXXX} to 4 digit \x{XXXX}.
        $pattern = preg_replace_callback('/\\\\x\{([0-9A-Fa-f]{5,})\}/', function (array $matches) {
            return '\\x{'.substr($matches[1], -4).'}';
        }, $pattern);

        return $pattern;
    }

    private function buildAnchorCache(): array
    {
        $A0_G0 = [];
        $A0_G1 = [];
        $A1_G0 = [];
        $A1_G1 = [];

        $len = strlen($this->pattern);

        for ($pos = 0; $pos < $len; $pos++) {
            $ch = $this->pattern[$pos];

            $A0_G0[$pos] = $ch;
            $A0_G1[$pos] = $ch;
            $A1_G0[$pos] = $ch;
            $A1_G1[$pos] = $ch;

            if ($ch === '\\') {
                if ($pos + 1 < $len) {
                    $nextCh = $this->pattern[$pos + 1];

                    if ($nextCh === 'A') {
                        $A0_G0[$pos + 1] = "\u{FFFF}";
                        $A0_G1[$pos + 1] = "\u{FFFF}";
                        $A1_G0[$pos + 1] = 'A';
                        $A1_G1[$pos + 1] = 'A';
                    } elseif ($nextCh === 'G') {
                        $A0_G0[$pos + 1] = "\u{FFFF}";
                        $A0_G1[$pos + 1] = 'G';
                        $A1_G0[$pos + 1] = "\u{FFFF}";
                        $A1_G1[$pos + 1] = 'G';
                    } else {
                        $A0_G0[$pos + 1] = $nextCh;
                        $A0_G1[$pos + 1] = $nextCh;
                        $A1_G0[$pos + 1] = $nextCh;
                        $A1_G1[$pos + 1] = $nextCh;
                    }

                    $pos++;
                }
            }
        }

        return [
            'A0_G0' => implode('', $A0_G0),
            'A0_G1' => implode('', $A0_G1),
            'A1_G0' => implode('', $A1_G0),
            'A1_G1' => implode('', $A1_G1),
        ];
    }

    private function resolveAnchors(string $pattern, bool $allowA, bool $allowG): string
    {
        if (! $this->hasAnchor || ! $this->anchorCache) {
            return $pattern;
        }

        if ($allowA) {
            if ($allowG) {
                return $this->anchorCache['A1_G1'];
            } else {
                return $this->anchorCache['A1_G0'];
            }
        }

        if ($allowG) {
            return $this->anchorCache['A0_G1'];
        }

        return $this->anchorCache['A0_G0'];
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
