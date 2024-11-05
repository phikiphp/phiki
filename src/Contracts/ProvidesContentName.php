<?php

namespace Phiki\Contracts;

interface ProvidesContentName
{
    /**
     * Get the name to apply to nested content.
     */
    public function getContentName(): ?string;
}
