<?php

namespace Phiki\TextMate;

use Phiki\Token\Token;

class LineTokens
{
    /**
     * The index of the final token in the line.
     */
    private int $lastTokenEndIndex = 0;

    /**
     * Create a new instance.
     * 
     * @param array<Token> $tokens
     */
    public function __construct(
        public string $lineText,
        public array $tokens = [],
    ) {}

    /**
     * Produce a set of tokens from the given state stack.
     */
    public function produce(StateStack $stack, int $endIndex): void
    {
        $this->produceFromScopes($stack->contentNameScopesList, $endIndex);
    }

    /**
     * Produce a set of tokens from the given scope list.
     */
    public function produceFromScopes(AttributedScopeStack | null $scopesList, int $endIndex): void
    {
        if ($this->lastTokenEndIndex >= $endIndex) {
            return;
        }

        $scopes = $scopesList?->getScopeNames() ?? [];

        $this->tokens[] = new Token(
            scopes: $scopes,
            text: substr($this->lineText, $this->lastTokenEndIndex, $endIndex - $this->lastTokenEndIndex),
            start: $this->lastTokenEndIndex,
            end: $endIndex,
        );

        $this->lastTokenEndIndex = $endIndex;
    }

    /**
     * Get all of the tokens in this line.
     * 
     * @return array<Token>
     */
    public function getResult(StateStack $stack, int $lineLength): array
    {
        if (count($this->tokens) > 0 && $this->tokens[count($this->tokens) - 1]->start === $lineLength) {
            array_pop($this->tokens);
        }

        if (count($this->tokens) === 0) {
            $this->lastTokenEndIndex = -1;
            $this->produce($stack, $lineLength);
            $this->tokens[count($this->tokens) - 1]->start = 0;
        }

        return $this->tokens;
    }
}
