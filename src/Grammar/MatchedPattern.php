<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternInterface;

class MatchedPattern
{
    public function __construct(
        public PatternInterface $pattern,
        public array $matches,
    ) {}

    /**
     * Get the matched text.
     */
    public function text(): string
    {
        return $this->matches[0][0];
    }

    public function end(): int
    {
        return $this->matches[0][1] + strlen($this->matches[0][0]);
    }

    /**
     * Get the start position of the matched pattern.
     */
    public function offset(): int
    {
        return $this->matches[0][1];
    }

    public function getCaptureGroup(int|string $index): ?array
    {
        return $this->matches[$index] ?? null;
    }
}
