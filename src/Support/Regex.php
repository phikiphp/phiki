<?php

namespace Phiki\Support;

use Phiki\Exceptions\FailedToSetSearchPositionException;
use Phiki\Exceptions\GenericPatternException;
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

    public function match(string $input, mixed &$matches = [], int $offset = 0, bool $allowA = false, bool $allowG = false, array $references = []): bool
    {
        $pattern = $this->get($allowA, $allowG);

        if ($references !== []) {
            $pattern = preg_replace_callback('/\\\\(\d+)/', function ($matches) use ($references) {
                if (! isset($references[$matches[1]][0])) {
                    return '';
                }

                return $references[$matches[1]][0];
            }, $pattern);
        }

        // Throw for warnings.
        set_error_handler(fn ($errno, $errstr) => throw new GenericPatternException($pattern, $errstr, $errno));

        mb_ereg_search_init($input, $pattern);
        
        if (! mb_ereg_search_setpos($offset)) {
            throw new FailedToSetSearchPositionException();
        }

        $result = mb_ereg_search_pos();

        restore_error_handler();

        if ($result === false) {
            return false;
        }
        
        [$start, $length] = $result;

        $matches = mb_ereg_search_getregs();

        // Since we know the start position and length of the match, we can
        // extract the relevant portion of the input string to reduce the
        // search grid for subsequent matches.
        $substr = mb_substr($input, $start, $length);
        
        foreach ($matches as $key => $match) {
            // The first match is the full match, so we can just use the start position.
            if ($key === 0) {
                $matches[$key] = [$match, $start];
                continue;
            }

            // If the capture group is empty, we need to use the same format as PCRE's PREG_OFFSET_CAPTURE,
            // which is an array with an empty match and -1 as the offset.
            if (! $match) {
                $matches[$key] = ["", -1];

                continue;
            }

            // For subsequent matches, we can use the reduced search grid to find the position
            // of the match within the substring. We need to adjust the position based on the
            // original input string's start position.
            $pos = mb_strpos($substr, $match);

            // We can then store the value in the matches array with the adjusted position.
            $matches[$key] = [$match, $start + $pos];
        }

        return true;
    }

    public function get(bool $allowA = false, bool $allowG = false): string
    {
        return $this->resolveAnchors($this->pattern, $allowA, $allowG);
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
