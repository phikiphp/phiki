<?php

namespace Phiki\Contracts;

use Phiki\Grammar\MatchedPattern;
use Phiki\TextMate\Engines\Engine;

interface RegexEngineInterface
{
    /**
     * The engine whose bundled grammars should be loaded.
     */
    public function identifier(): Engine;

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
