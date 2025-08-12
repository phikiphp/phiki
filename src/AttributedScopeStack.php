<?php

namespace Phiki;

class AttributedScopeStack
{
    /**
     * Create a new instance.
     */
    private function __construct(
        public readonly AttributedScopeStack | null $parent,
        public readonly ScopeStack $scopePath,
    ) {}

    /**
     * Get the scope names for this stack.
     * 
     * @return list<string>
     */
    public function getScopeNames(): array
    {
        return $this->scopePath->getSegments();
    }
}
