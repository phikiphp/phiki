<?php

namespace Phiki\TextMate\Engines;

use Phiki\Contracts\RegexEngineInterface;
use Phiki\Grammar\MatchedPattern;

/**
 * Matches patterns with PCRE, which every PHP build includes. The bundled grammars for this
 * engine are translated from Oniguruma syntax ahead of time, by `meta/translate-grammars.php`.
 */
class PcreEngine implements RegexEngineInterface
{
    /**
     * PCRE delimiter. Translated grammar patterns never contain it.
     */
    public const DELIMITER = "\x01";

    /**
     * `u` matches Unicode code points and properties, `m` makes `^` and `$` line anchors
     * as they always are in Oniguruma, and `J` allows duplicate group names.
     */
    protected const FLAGS = 'umJ';

    public function identifier(): string
    {
        return 'pcre';
    }

    public function findNextMatch(array $patterns, string $lineText, int $linePos): ?MatchedPattern
    {
        $bestMatches = null;
        $bestPattern = null;

        foreach ($patterns as [$pattern, $regex]) {
            // Patterns that fail to compile are skipped, the same as a pattern that does not match.
            if (@preg_match(self::DELIMITER.$regex.self::DELIMITER.self::FLAGS, $lineText, $matches, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL, $linePos) !== 1) {
                continue;
            }

            // The earliest match wins, and the first pattern wins a tie.
            if ($bestMatches === null || $matches[0][1] < $bestMatches[0][1]) {
                $bestMatches = $matches;
                $bestPattern = $pattern;
            }

            if ($matches[0][1] === $linePos) {
                break;
            }
        }

        if ($bestPattern === null) {
            return null;
        }

        $wellFormedMatches = [];

        foreach ($bestMatches as $key => [$match, $offset]) {
            // Named groups are also returned under their number, so we only keep the numbered keys.
            if (is_string($key)) {
                continue;
            }

            $wellFormedMatches[$key] = $match === null ? ['', -1] : [$match, $offset];
        }

        return new MatchedPattern($bestPattern, $wellFormedMatches);
    }
}
