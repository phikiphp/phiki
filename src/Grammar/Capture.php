<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternCollectionInterface;

class Capture implements PatternCollectionInterface
{
    public function __construct(
        public string $index,
        public ?string $name,
        public array $patterns = [],
    ) {}

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    public function hasPatterns(): bool
    {
        return count($this->patterns) > 0;
    }

    public function scope(): ?array
    {
        return $this->name ? explode(' ', $this->name) : null;
    }
}
