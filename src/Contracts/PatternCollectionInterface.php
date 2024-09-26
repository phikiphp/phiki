<?php

namespace Phiki\Contracts;

interface PatternCollectionInterface
{
    /**
     * Get the collection of child patterns.
     * 
     * @return \Phiki\Grammar\Pattern[]
     */
    public function getPatterns(): array;

    /**
     * Determine if the pattern has child patterns.
     * 
     * @return bool
     */
    public function hasPatterns(): bool;
}