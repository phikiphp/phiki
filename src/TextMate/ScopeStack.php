<?php

namespace Phiki\TextMate;

use Stringable;

class ScopeStack implements Stringable
{
    /**
     * @var list<string>|null
     */
    protected ?array $segments = null;

    /**
     * Stacks already pushed onto this one, so pushing the same scope again reuses the same
     * node, and the tokens on it share one list of segments.
     *
     * @var array<string, ScopeStack>
     */
    protected array $children = [];

    /**
     * Create a new instance.
     */
    public function __construct(
        public ?ScopeStack $parent,
        public string $scopeName,
    ) {}

    /**
     * Push a new scope onto the stack.
     */
    public function push(string $scopeName): ScopeStack
    {
        return $this->children[$scopeName] ??= new ScopeStack($this, $scopeName);
    }

    /**
     * Get the scope name segments in the stack.
     *
     * @return list<string>
     */
    public function getSegments(): array
    {
        if ($this->segments !== null) {
            return $this->segments;
        }

        $stack = $this;
        $result = [];

        while ($stack !== null) {
            $result[] = $stack->scopeName;
            $stack = $stack->parent;
        }

        return $this->segments = array_reverse($result);
    }

    /**
     * Get a string representation of the stack.
     */
    public function __toString(): string
    {
        return implode(' ', $this->getSegments());
    }
}
