<?php

namespace Phiki\Support;

use Stringable;

class Regex implements Stringable
{
    protected bool $hasAnchor;

    protected array $anchorCache = [];

    protected string $pattern;

    public function __construct(string $pattern)
    {
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

    public function get(bool $allowA = false, bool $allowG = false, array $references = []): string
    {
        $pattern = $this->resolveAnchors($this->pattern, $allowA, $allowG);

        $pattern = preg_replace_callback('/\\\\(\d+)/', function ($matches) use ($references) {
            if (! isset($references[$matches[1]][0])) {
                return '';
            }

            return $references[$matches[1]][0];
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
