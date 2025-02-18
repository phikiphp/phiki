<?php

namespace Phiki\Contracts;

use Phiki\Grammar\MatchedPattern;
use Phiki\Tokenizer;
use Stringable;

interface PatternInterface extends Stringable
{
    /**
     * Attempt to match the pattern against the current line's text, starting from the given position.
     */
    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false;

    /**
     * Produce a new stack of scopes based on the current stack and the pattern's scope.
     *
     * @param  string[]  $scopes
     * @return string[]
     */
    public function produceScopes(array $scopes): array;

    /**
     * Return the scope that this pattern applies.
     */
    public function scope(): string|array|null;

    /**
     * Determine whether or not the pattern was injected.
     */
    public function wasInjected(): bool;
}
