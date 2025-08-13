<?php

namespace Phiki\Contracts;

interface HasContentNameInterface
{
    /**
     * Get the content name for the pattern.
     * 
     * @param array<array{ 0: string, 1: int }> $captures
     */
    public function getContentName(array $captures): ?string;
}
