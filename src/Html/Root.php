<?php

namespace Phiki\Html;

use Stringable;

class Root implements Stringable
{
    public function __construct(
        public array $children = []
    ) {}

    public function __toString(): string
    {
        return implode('', $this->children);
    }
}