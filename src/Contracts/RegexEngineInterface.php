<?php

namespace Phiki\Contracts;

use Phiki\Grammar\MatchedPattern;

interface RegexEngineInterface
{
    /**
     * The name of the directory in `resources/grammars` holding the bundled grammars written
     * for this engine, such as `oniguruma` or `pcre`.
     */
    public function identifier(): string;

    /**
     * Find the earliest match of the given patterns in the line, starting the search at the
     * given byte offset. When several patterns match at the same offset, the first one wins.
     *
     * Patterns come from the grammars loaded for this engine. Captures are returned
     * in the same format as PREG_OFFSET_CAPTURE, with ['', -1] for groups that did not match.
     *
     * @param  array<array{0: PatternInterface, 1: string}>  $patterns
     */
    public function findNextMatch(array $patterns, string $lineText, int $linePos): ?MatchedPattern;
}
