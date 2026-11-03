<?php

namespace Phiki\Contracts;

use Phiki\Grammar\MatchedPattern;

interface RegexEngineInterface
{
    /**
     * Find the earliest match of the given patterns in the line, starting the search at the
     * given byte offset. When several patterns match at the same offset, the first one wins.
     *
     * Patterns are written in Oniguruma syntax, as in TextMate grammars. Captures are returned
     * in the same format as PREG_OFFSET_CAPTURE, with ['', -1] for groups that did not match.
     *
     * @param  array<array{0: PatternInterface, 1: string}>  $patterns
     */
    public function findNextMatch(array $patterns, string $lineText, int $linePos): ?MatchedPattern;
}
