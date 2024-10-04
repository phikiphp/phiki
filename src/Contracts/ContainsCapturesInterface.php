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
     */
    public function hasCaptures(): bool;
}
