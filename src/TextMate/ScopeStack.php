<?php

namespace Phiki\TextMate;

use Stringable;

class ScopeStack implements Stringable
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public ScopeStack | null $parent,
        public string $scopeName,
    ) {}

    /**
     * Push a new scope onto the stack.
     */
    public function push(string $scopeName): ScopeStack
    {
        return new ScopeStack($this, $scopeName);
    }

    /**
     * Get the scope name segments in the stack.
     * 
     * @return list<string>
     */
    public function getSegments(): array
    {
        $stack = $this;
        $result = [];

        while ($stack !== null) {
            $result[] = $stack->scopeName;
            $stack = $stack->parent;
        }

        return array_reverse($result);
    }

    /**
     * Get a string representation of the stack.
     */
    public function __toString(): string
    {
        return implode(' ', $this->getSegments());
    }
}
