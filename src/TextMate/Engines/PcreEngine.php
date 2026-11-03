<?php

namespace Phiki\TextMate\Engines;

use Phiki\Contracts\RegexEngineInterface;
use Phiki\Grammar\MatchedPattern;
use Phiki\Support\OnigurumaToPcre;

/**
 * Matches patterns with PCRE, which every PHP build includes, by translating them from
 * Oniguruma syntax first.
 */
class PcreEngine implements RegexEngineInterface
{
    /**
     * PCRE delimiter. Grammar patterns never contain it once raw occurrences are escaped.
     */
    protected const DELIMITER = "\x01";

    /**
     * `u` matches Unicode code points and properties, `m` makes `^` and `$` line anchors
     * as they always are in Oniguruma, and `J` allows duplicate group names.
     */
    protected const FLAGS = 'umJ';

    /**
     * The most translated patterns to keep. `end` patterns embed text captured by their
     * `begin` pattern, so a long-running process would otherwise grow the cache forever.
     */
    protected const CACHE_SIZE = 10000;

    /**
     * @var array<string, string>
     */
    protected static array $translated = [];

    public function findNextMatch(array $patterns, string $lineText, int $linePos): ?MatchedPattern
    {
        $bestMatches = null;
        $bestPattern = null;

        foreach ($patterns as [$pattern, $regex]) {
            // Patterns that fail to compile are skipped, the same as a pattern that does not match.
            if (@preg_match($this->translate($regex), $lineText, $matches, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL, $linePos) !== 1) {
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

    protected function translate(string $regex): string
    {
        if (isset(static::$translated[$regex])) {
            return static::$translated[$regex];
        }

        if (count(static::$translated) >= self::CACHE_SIZE) {
            static::$translated = [];
        }

        // Some grammars contain the raw delimiter character, so spell it as an escape instead.
        $pcre = OnigurumaToPcre::translate(str_replace(self::DELIMITER, '\\x01', $regex));

        return static::$translated[$regex] = self::DELIMITER.$pcre.self::DELIMITER.self::FLAGS;
    }
}
