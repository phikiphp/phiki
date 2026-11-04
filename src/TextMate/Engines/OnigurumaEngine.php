<?php

namespace Phiki\TextMate\Engines;

use Phiki\Contracts\RegexEngineInterface;
use Phiki\Exceptions\FailedToInitializePatternSearchException;
use Phiki\Exceptions\FailedToSetSearchPositionException;
use Phiki\Exceptions\UnsupportedRegexEngineException;
use Phiki\Grammar\MatchedPattern;
use Phiki\TextMate\SearchResultCache;

/**
 * Matches patterns with Oniguruma through the `mb_ereg_search_*` functions, provided by
 * mbstring's mbregex or the mb_onig extension. PHP 8.6 deprecates mbregex.
 */
class OnigurumaEngine implements RegexEngineInterface
{
    public function __construct()
    {
        if (! self::isSupported()) {
            throw new UnsupportedRegexEngineException('The Oniguruma engine needs the mb_ereg_search_* functions, from mbstring\'s mbregex or the mb_onig extension. Use the PCRE engine instead.');
        }
    }

    public function identifier(): Engine
    {
        return Engine::Oniguruma;
    }

    public static function isSupported(): bool
    {
        return function_exists('mb_ereg_search_init');
    }

    public function findNextMatch(array $patterns, string $lineText, int $linePos, ?SearchResultCache $cache = null): ?MatchedPattern
    {
        $bestLocation = null;
        $bestMatches = null;
        $bestPattern = null;
        $bestRegex = null;

        // Initializing the search copies the whole line, which adds up on long lines, so it's
        // skipped when the cache says the line is already the one being searched.
        if (($cache?->useLine($lineText) ?? true) && ! mb_ereg_search_init($lineText)) {
            throw new FailedToInitializePatternSearchException;
        }

        foreach ($patterns as [$pattern, $regex]) {
            $cached = $cache?->get($regex, $linePos);
            $searched = $cached === null;

            if ($searched) {
                if (! mb_ereg_search_setpos($linePos)) {
                    throw new FailedToSetSearchPositionException;
                }

                $result = @mb_ereg_search_pos($regex);
                $start = $result === false ? false : $result[0];

                $cache?->put($regex, $linePos, $start);
            } else {
                $start = $cached === false ? false : $cached[0];
            }

            if ($start === false) {
                continue;
            }

            if ($start === $linePos || $start < $bestLocation || $bestLocation === null) {
                $bestLocation = $start;
                // Only the captures of the search that just ran can be read, so a cached result is searched again below.
                $bestMatches = $searched ? mb_ereg_search_getregs() : null;
                $bestPattern = $pattern;
                $bestRegex = $regex;

                if ($start === $linePos) {
                    break;
                }
            }
        }

        if ($bestPattern === null) {
            return null;
        }

        if ($bestMatches === null) {
            if (! mb_ereg_search_setpos($bestLocation)) {
                throw new FailedToSetSearchPositionException;
            }

            $bestMatches = @mb_ereg_search_pos($bestRegex) !== false ? mb_ereg_search_getregs() : false;
        }

        // mbregex reports byte offsets but not where each capture group starts, so we search for the
        // captures in the line from the start of the match. Captures inside a lookahead can end up
        // after the match, so the search covers the rest of the line.
        $substr = substr($lineText, $bestLocation);
        $substrLength = strlen($substr);
        $keyToIndexMap = array_flip(array_keys($bestMatches));
        $wellFormedMatches = [];
        $previousStart = 0;
        $previousEnd = 0;

        foreach ($bestMatches as $key => $match) {
            // The first match is the full match, so we can just use the start position.
            if ($key === 0) {
                $wellFormedMatches[$key] = [$match, $bestLocation];

                continue;
            }

            $key = is_string($key) ? $keyToIndexMap[$key] : $key;

            // If the capture group is empty, we need to use the same format as PCRE's PREG_OFFSET_CAPTURE,
            // which is an array with an empty match and -1 as the offset.
            if ($match === false || $match === '') {
                $wellFormedMatches[$key] = ['', -1];

                continue;
            }

            // For subsequent matches, we can use the reduced search grid to find the position
            // of the match within the substring. Capture groups are numbered by opening
            // parenthesis, so capture N + 1 can never start before capture N. Searching from
            // a cursor instead of from the start keeps a capture that repeats earlier text -
            // the closing delimiter of `(\*)([^*]+)(\*)`, for example - from resolving to the
            // position of the opening one.
            $pos = strpos($substr, $match, min($previousStart, $substrLength));

            // A candidate starting exactly where the previous capture did, but longer than it,
            // cannot be nested inside it, so the real match has to be further along.
            if ($pos === $previousStart && strlen($match) > $previousEnd - $previousStart) {
                $sibling = strpos($substr, $match, min($previousEnd, $substrLength));

                if ($sibling !== false) {
                    $pos = $sibling;
                }
            }

            if ($pos === false) {
                $pos = (int) strpos($substr, $match);
            }

            $previousStart = $pos;
            $previousEnd = max($previousEnd, $pos + strlen($match));

            // We can then store the value in the matches array with the adjusted position.
            $wellFormedMatches[$key] = [$match, $bestLocation + $pos];
        }

        return new MatchedPattern($bestPattern, $wellFormedMatches);
    }
}
