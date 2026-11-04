<?php

namespace Phiki\TextMate\Engines;

use Phiki\Contracts\RegexEngineInterface;
use Phiki\Grammar\MatchedPattern;
use Phiki\TextMate\SearchResultCache;

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
    public const FLAGS = 'umJ';

    /**
     * Prefix of the groups the translator wraps subexpression calls in, followed by a counter
     * and the number of the called group, such as `_phikicall0_3`.
     */
    public const CALL_GROUP_PREFIX = '_phikicall';

    public function identifier(): Engine
    {
        return Engine::Pcre;
    }

    public function findNextMatch(array $patterns, string $lineText, int $linePos, ?SearchResultCache $cache = null): ?MatchedPattern
    {
        $bestMatches = null;
        $bestPattern = null;

        $cache?->useLine($lineText);

        foreach ($patterns as [$pattern, $regex]) {
            $cached = $cache?->get($regex, $linePos);

            if ($cached === null) {
                // Patterns that fail to compile are skipped, the same as a pattern that does not match.
                $matched = @preg_match(self::DELIMITER.$regex.self::DELIMITER.self::FLAGS, $lineText, $matches, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL, $linePos) === 1;

                $cache?->put($regex, $linePos, $matched ? $matches[0][1] : false, $matched ? $matches : null);
            } else {
                $matched = $cached !== false;
                $matches = $matched ? $cached[1] : [];
            }

            if (! $matched) {
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
        $calledGroup = null;
        $calls = [];

        foreach ($bestMatches as $key => [$match, $offset]) {
            // Named groups are also returned under their number, just after their name, so we only keep the
            // numbered keys. The translator wraps each subexpression call in a group named after the group it
            // calls; those groups are left out, so the others keep the numbers they have in the grammar.
            if (is_string($key)) {
                if (str_starts_with($key, self::CALL_GROUP_PREFIX)) {
                    $calledGroup = (int) substr($key, strrpos($key, '_') + 1);
                }

                continue;
            }

            if ($calledGroup !== null) {
                if ($match !== null) {
                    $calls[$calledGroup][] = [$match, $offset];
                }

                $calledGroup = null;

                continue;
            }

            $wellFormedMatches[] = $match === null ? ['', -1] : [$match, $offset];
        }

        // Oniguruma sets a group's capture whenever it runs, including through a call, so the capture
        // that ends last wins.
        foreach ($calls as $group => $captures) {
            foreach ($captures as $capture) {
                $current = $wellFormedMatches[$group] ?? null;

                if ($current !== null && ($current[1] < 0 || $capture[1] + strlen($capture[0]) > $current[1] + strlen($current[0]))) {
                    $wellFormedMatches[$group] = $capture;
                }
            }
        }

        return new MatchedPattern($bestPattern, $wellFormedMatches);
    }
}
