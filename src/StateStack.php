<?php

namespace Phiki;

use Phiki\Grammar\Pattern;

class StateStack
{
    /**
     * The depth in the list of this state stack.
     */
    public readonly int $depth;

    /**
     * Create a new instance.
     */
    public function __construct(
        public readonly StateStack | null $parent,
        public readonly Pattern $pattern,
        public readonly int $enterPos,
        public readonly int $anchorPos,
        public readonly bool $beginRuleCapturedEOL,
        public readonly string | null $endRule,
        public readonly AttributedScopeStack | null $nameScopesList,
        public readonly AttributedScopeStack | null $contentNameScopesList,
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
    public function push(Pattern $pattern, int $enterPos, int $anchorPos, bool $beginRuleCapturedEOL, string | null $endRule, AttributedScopeStack | null $nameScopesList, AttributedScopeStack | null $contentNameScopesList): StateStack
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
    public function withEndRule(string $endRule): StateStack
    {
        $stack = clone $this;
        $stack->endRule = $endRule;

        return $stack;
    }

    /**
     * Check whether this state stack has the same rule as the given state stack.
     */
    public function hasSameRuleAs(StateStack $other): bool
    {
        $el = $this;

        while ($el !== null && $el->enterPos === $other->enterPos) {
            if ($el->pattern === $other->pattern) {
                return true;
            }

            $el = $el->parent;
        }

        return false;
    }
}
