<?php

namespace Phiki\TextMate;

use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;

class AttributedScopeStack
{
    /**
     * Create a new instance.
     */
    private function __construct(
        public AttributedScopeStack | null $parent,
        public ScopeStack $scopePath,
    ) {}

    /**
     * Push a new scope onto the stack.
     */
    public function push(?string $scopeName): AttributedScopeStack
    {
        if ($scopeName === null) {
            return $this;
        }

        if (! str_contains($scopeName, ' ')) {
            return new AttributedScopeStack($this, $this->scopePath->push($scopeName));
        }

        $scopeNames = explode(' ', $scopeName);
        $result = $this;

        foreach ($scopeNames as $name) {
            $result = new AttributedScopeStack($result, $result->scopePath->push($name));
        }

        return $result;
    }

    /**
     * Get the scope names for this stack.
     * 
     * @return list<string>
     */
    public function getScopeNames(): array
    {
        return $this->scopePath->getSegments();
    }

    /**
     * Create the root scope stack.
     */
    public static function createRoot(string $rootScopeName): AttributedScopeStack
    {
        return new AttributedScopeStack(null, new ScopeStack(null, $rootScopeName));
    }
}
