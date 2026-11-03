<?php

namespace Phiki\TextMate;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
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
                [$this->pattern, $this->pattern->while->get($this->allowA, $this->allowG, $this->pattern->begin->matches)],
            ] : $this->pattern->compile($this->grammar, $this->grammars, $this->allowA, $this->allowG);
        $bestMatches = null;
        $bestPattern = null;

        foreach ($patterns as [$pattern, $regex]) {
            // Patterns that fail to compile are skipped, the same as a pattern that does not match.
            if (@preg_match($regex, $lineText, $matches, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL, $linePos) !== 1) {
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

            // Unmatched groups use an empty match and -1 as the offset, like PREG_OFFSET_CAPTURE.
            $wellFormedMatches[$key] = $match === null ? ['', -1] : [$match, $offset];
        }

        return new MatchedPattern($bestPattern, $wellFormedMatches);
    }
}
