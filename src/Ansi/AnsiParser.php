<?php

namespace Phiki\Ansi;

class AnsiParser
{
    protected const DECORATIONS = [
        1 => 'bold',
        2 => 'dim',
        3 => 'italic',
        4 => 'underline',
        5 => 'blink',
        7 => 'reverse',
        8 => 'hidden',
        9 => 'strikethrough',
    ];

    protected const NAMED_COLORS = [
        'black',
        'red',
        'green',
        'yellow',
        'blue',
        'magenta',
        'cyan',
        'white',
        'brightBlack',
        'brightRed',
        'brightGreen',
        'brightYellow',
        'brightBlue',
        'brightMagenta',
        'brightCyan',
        'brightWhite',
    ];

    protected ?AnsiColor $foreground = null;

    protected ?AnsiColor $background = null;

    /** @var array<string> */
    protected array $decorations = [];

    /**
     * @return array<ParsedAnsiToken>
     */
    public function parse(string $text): array
    {
        $tokens = [];
        $position = 0;
        $length = strlen($text);

        while ($position < $length) {
            $sequence = $this->findSequence($text, $position);

            if ($sequence === null) {
                // No more sequences, consume rest of text
                $remaining = substr($text, $position);
                if ($remaining !== '') {
                    $tokens[] = $this->createToken($remaining);
                }
                break;
            }

            // Consume text before sequence
            if ($sequence['start'] > $position) {
                $before = substr($text, $position, $sequence['start'] - $position);
                if ($before !== '') {
                    $tokens[] = $this->createToken($before);
                }
            }

            // Process the escape sequence
            $this->processSequence($sequence['codes']);

            $position = $sequence['end'];
        }

        return $tokens;
    }

    /**
     * @return array{start: int, end: int, codes: array<string>}|null
     */
    protected function findSequence(string $text, int $position): ?array
    {
        // Look for ESC[ or \x1b[ or \033[
        $escapePos = strpos($text, "\x1b[", $position);

        if ($escapePos === false) {
            return null;
        }

        // Find the end of the sequence (marked by 'm')
        $endPos = strpos($text, 'm', $escapePos + 2);

        if ($endPos === false) {
            return null;
        }

        $codesString = substr($text, $escapePos + 2, $endPos - $escapePos - 2);
        $codes = $codesString === '' ? ['0'] : explode(';', $codesString);

        return [
            'start' => $escapePos,
            'end' => $endPos + 1,
            'codes' => $codes,
        ];
    }

    /**
     * @param  array<string>  $codes
     */
    protected function processSequence(array $codes): void
    {
        while (count($codes) > 0) {
            $code = array_shift($codes);
            $codeInt = (int) $code;

            if ($codeInt === 0) {
                // Reset all
                $this->foreground = null;
                $this->background = null;
                $this->decorations = [];
            } elseif ($codeInt >= 1 && $codeInt <= 9) {
                // Set decoration
                if (isset(self::DECORATIONS[$codeInt])) {
                    $decoration = self::DECORATIONS[$codeInt];
                    if (! in_array($decoration, $this->decorations, true)) {
                        $this->decorations[] = $decoration;
                    }
                }
            } elseif ($codeInt >= 21 && $codeInt <= 29) {
                // Reset decoration
                $resetCode = $codeInt - 20;
                if (isset(self::DECORATIONS[$resetCode])) {
                    $decoration = self::DECORATIONS[$resetCode];
                    $this->decorations = array_values(array_filter(
                        $this->decorations,
                        fn ($d) => $d !== $decoration,
                    ));
                    // dim resets both dim and bold
                    if ($decoration === 'dim') {
                        $this->decorations = array_values(array_filter(
                            $this->decorations,
                            fn ($d) => $d !== 'bold',
                        ));
                    }
                }
            } elseif ($codeInt >= 30 && $codeInt <= 37) {
                // Standard foreground color
                $this->foreground = new NamedAnsiColor(self::NAMED_COLORS[$codeInt - 30]);
            } elseif ($codeInt === 38) {
                // Extended foreground color
                $this->foreground = $this->parseExtendedColor($codes);
            } elseif ($codeInt === 39) {
                // Reset foreground
                $this->foreground = null;
            } elseif ($codeInt >= 40 && $codeInt <= 47) {
                // Standard background color
                $this->background = new NamedAnsiColor(self::NAMED_COLORS[$codeInt - 40]);
            } elseif ($codeInt === 48) {
                // Extended background color
                $this->background = $this->parseExtendedColor($codes);
            } elseif ($codeInt === 49) {
                // Reset background
                $this->background = null;
            } elseif ($codeInt >= 90 && $codeInt <= 97) {
                // Bright foreground color
                $this->foreground = new NamedAnsiColor(self::NAMED_COLORS[$codeInt - 90 + 8]);
            } elseif ($codeInt >= 100 && $codeInt <= 107) {
                // Bright background color
                $this->background = new NamedAnsiColor(self::NAMED_COLORS[$codeInt - 100 + 8]);
            }
        }
    }

    /**
     * @param  array<string>  $codes
     */
    protected function parseExtendedColor(array &$codes): ?AnsiColor
    {
        if (count($codes) === 0) {
            return null;
        }

        $mode = array_shift($codes);

        if ($mode === '5') {
            // 256-color mode
            if (count($codes) === 0) {
                return null;
            }
            $index = (int) array_shift($codes);

            return new IndexedAnsiColor($index);
        }

        if ($mode === '2') {
            // RGB mode
            if (count($codes) < 3) {
                return null;
            }
            $r = (int) array_shift($codes);
            $g = (int) array_shift($codes);
            $b = (int) array_shift($codes);

            return new RgbAnsiColor($r, $g, $b);
        }

        return null;
    }

    protected function createToken(string $text): ParsedAnsiToken
    {
        return new ParsedAnsiToken(
            $text,
            $this->foreground,
            $this->background,
            $this->decorations,
        );
    }

    public function reset(): void
    {
        $this->foreground = null;
        $this->background = null;
        $this->decorations = [];
    }
}
