<?php

namespace Phiki\Contracts;

interface ContainsCapturesInterface
{
    /**
     * Get the captures for this pattern.
     * 
     * @return \Phiki\Grammar\Capture[]
     */
    public function getCaptures(): array;

    /**
     * Check if the pattern has captures.
     * 
     * @return bool
     */
    public function hasCaptures(): bool;
}