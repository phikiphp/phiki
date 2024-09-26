<?php

namespace Phiki\Contracts;

use Phiki\MatchedPattern;
use Phiki\Tokenizer;

interface PatternInterface
{
    /**
     * Attempt to match the pattern against the current line's text, starting from the given position.
     * 
     * @return MatchedPattern|false
     */
    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern | false;

    /**
     * Produce a new stack of scopes based on the current stack and the pattern's scope.
     * 
     * @param string[] $scopes
     * @return string[]
     */
    public function produceScopes(array $scopes): array;

    /**
     * Return the scope that this pattern applies.
     * 
     * @return string|null
     */
    public function scope(): ?string;
}