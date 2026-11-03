<?php

namespace Phiki\Support;

use Stringable;

class Regex implements Stringable
{
    protected bool $hasAnchor;

    protected array $anchorCache = [];

    protected string $pattern;

    protected bool $compiled = false;

    /**
     * PCRE delimiter. Grammar patterns never contain it, so they need no escaping.
     */
    protected const DELIMITER = "\x01";

    /**
     * `u` matches Unicode code points and properties, `m` makes `^` and `$` line anchors
     * as they always are in Oniguruma, and `J` allows duplicate group names.
     */
    protected const FLAGS = 'umJ';

    public function __construct(protected string $source) {}

    /**
     * Translate the pattern to PCRE. Most patterns in a grammar are never used, so this
     * happens the first time a pattern is needed rather than when the grammar is parsed.
     */
    protected function compile(): void
    {
        $this->compiled = true;

        // Some grammars contain the raw delimiter character, so spell it as an escape instead.
        $pattern = OnigurumaToPcre::translate(str_replace(self::DELIMITER, '\\x01', $this->source));
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

        if ($this->hasAnchor) {
            $this->anchorCache = $this->buildAnchorCache();
        }
    }

    /**
     * Get the PCRE pattern. Back-references are only replaced with the given captures for
     * `end` patterns, which refer to the captures of their `begin` pattern.
     */
    public function get(bool $allowA = false, bool $allowG = false, ?array $references = null): string
    {
        if (! $this->compiled) {
            $this->compile();
        }

        $pattern = $this->resolveAnchors($this->pattern, $allowA, $allowG);

        if ($references === null) {
            return self::DELIMITER.$pattern.self::DELIMITER.self::FLAGS;
        }

        $pattern = preg_replace_callback('/\\\\(\d+)/', function ($matches) use ($references) {
            if (! isset($references[$matches[1]][0])) {
                return '';
            }

            return preg_quote($references[$matches[1]][0], self::DELIMITER);
        }, $pattern);

        return self::DELIMITER.$pattern.self::DELIMITER.self::FLAGS;
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

        return match (true) {
            $allowA && $allowG => $this->anchorCache['A1_G1'],
            $allowA => $this->anchorCache['A1_G0'],
            $allowG => $this->anchorCache['A0_G1'],
            default => $this->anchorCache['A0_G0'],
        };
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
