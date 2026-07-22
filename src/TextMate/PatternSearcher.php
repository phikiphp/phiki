<?php

namespace Phiki\TextMate;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Exceptions\FailedToInitializePatternSearchException;
use Phiki\Exceptions\FailedToSetSearchPositionException;
use Phiki\Grammar\MatchedPattern;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Grammar\WhilePattern;

class PatternSearcher
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected PatternInterface $pattern,
        protected ParsedGrammar $grammar,
        protected GrammarRepositoryInterface $grammars,
        protected bool $allowA,
        protected bool $allowG,
    ) {}

    /**
     * Find the next closest match in the given text.
     */
    public function findNextMatch(string $lineText, int $linePos, bool $while = false): ?MatchedPattern
    {
        $patterns = $while && $this->pattern instanceof WhilePattern
            ? [
                [$this->pattern, $this->pattern->while->get($this->allowA, $this->allowG)],
            ] : $this->pattern->compile($this->grammar, $this->grammars, $this->allowA, $this->allowG);
        $bestLocation = null;
        $bestLength = null;
        $bestMatches = null;
        $bestPattern = null;

        if (! mb_ereg_search_init($lineText)) {
            throw new FailedToInitializePatternSearchException;
        }

        foreach ($patterns as [$pattern, $regex]) {
            if (! mb_ereg_search_setpos($linePos)) {
                throw new FailedToSetSearchPositionException;
            }

            $result = @mb_ereg_search_pos($regex);

            if ($result === false) {
                continue;
            }

            [$start, $length] = $result;

            if ($start === $linePos) {
                $bestLocation = $start;
                $bestMatches = mb_ereg_search_getregs();
                $bestLength = $length;
                $bestPattern = $pattern;

                break;
            }

            if ($start < $bestLocation || $bestLocation === null) {
                $bestLocation = $start;
                $bestLength = $length;
                $bestMatches = mb_ereg_search_getregs();
                $bestPattern = $pattern;

                continue;
            }
        }

        if ($bestPattern === null) {
            return null;
        }

        // Since we know the start position and length of the match, we can
        // extract the relevant portion of the input string to reduce the
        // search grid for subsequent matches.
        $substr = mb_substr($lineText, $bestLocation, $bestLength);
        $substrLength = mb_strlen($substr);
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
            if (! $match) {
                $wellFormedMatches[$key] = ['', -1];

                continue;
            }

            // For subsequent matches, we can use the reduced search grid to find the position
            // of the match within the substring. Capture groups are numbered by opening
            // parenthesis, so capture N + 1 can never start before capture N. Searching from
            // a cursor instead of from the start keeps a capture that repeats earlier text -
            // the closing delimiter of `(\*)([^*]+)(\*)`, for example - from resolving to the
            // position of the opening one.
            $pos = mb_strpos($substr, $match, min($previousStart, $substrLength));

            // A candidate starting exactly where the previous capture did, but longer than it,
            // cannot be nested inside it, so the real match has to be further along.
            if ($pos === $previousStart && mb_strlen($match) > $previousEnd - $previousStart) {
                $sibling = mb_strpos($substr, $match, min($previousEnd, $substrLength));

                if ($sibling !== false) {
                    $pos = $sibling;
                }
            }

            if ($pos === false) {
                $pos = (int) mb_strpos($substr, $match);
            }

            $previousStart = $pos;
            $previousEnd = max($previousEnd, $pos + mb_strlen($match));

            // We can then store the value in the matches array with the adjusted position.
            $wellFormedMatches[$key] = [$match, $bestLocation + $pos];
        }

        return new MatchedPattern($bestPattern, $wellFormedMatches);
    }
}
