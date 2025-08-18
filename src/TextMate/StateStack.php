<?php

namespace Phiki\TextMate;

use Phiki\Contracts\PatternInterface;
use Phiki\Exceptions\IndeterminateStateException;
use Phiki\Grammar\EndPattern;
use Phiki\Grammar\WhilePattern;

class StateStack
{
    /**
     * The depth in the list of this state stack.
     */
    public int $depth;

    /**
     * Create a new instance.
     */
    public function __construct(
        public StateStack | null $parent,
        public PatternInterface $pattern,
        public int $enterPos,
        public int $anchorPos,
        public bool $beginRuleCapturedEOL,
        public string | null $endRule,
        public AttributedScopeStack | null $nameScopesList,
        public AttributedScopeStack | null $contentNameScopesList,
    ) {
        $this->depth = $parent ? $parent->depth + 1 : 0;
    }

    /**
     * Pop the current state stack.
     */
    public function pop(): StateStack | null
    {
        return $this->parent;
    }

    /**
     * Safely pop the current state stack.
     */
    public function safePop(): StateStack
    {
        if ($this->parent === null) {
            return $this;
        }

        return $this->parent;
    }

    /**
     * Push the given data into a new state stack.
     */
    public function push(PatternInterface $pattern, int $enterPos, int $anchorPos, bool $beginRuleCapturedEOL, string | null $endRule, AttributedScopeStack | null $nameScopesList, AttributedScopeStack | null $contentNameScopesList): StateStack
    {
        return new StateStack(
            parent: $this,
            pattern: $pattern,
            enterPos: $enterPos,
            anchorPos: $anchorPos,
            beginRuleCapturedEOL: $beginRuleCapturedEOL,
            endRule: $endRule,
            nameScopesList: $nameScopesList,
            contentNameScopesList: $contentNameScopesList,
        );
    }

    /**
     * Generate a near-identical state stack with the given content name scopes list.
     */
    public function withContentNameScopesList(AttributedScopeStack $contentNameScopesList): StateStack
    {
        $stack = clone $this;
        $stack->contentNameScopesList = $contentNameScopesList;

        return $stack;
    }

    /**
     * Generate a near-identical state stack with the given end rule.
     */
    public function withEndRule(EndPattern | WhilePattern $rule): StateStack
    {
        $stack = clone $this;
        $stack->pattern = $rule;

        return $stack;
    }

    /**
     * Check whether this state stack has the same rule as the given state stack.
     */
    public function hasSameRuleAs(StateStack $other): bool
    {
        $el = $this;

        while ($el !== null && $el->enterPos === $other->enterPos) {
            if ($el->pattern->getId() === $other->pattern->getId()) {
                return true;
            }

            $el = $el->parent;
        }

        return false;
    }

    /**
     * Reset the state stack.
     */
    public function reset(): void
    {
        $el = $this;

        while ($el !== null) {
            $el->enterPos = -1;
            $el->anchorPos = -1;
            $el = $el->parent;
        }
    }
}
