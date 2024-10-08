<?php

namespace Phiki\Html;

use Stringable;

class Root implements Stringable
{
    public function __construct(
        public Pre $pre,
    ) {}

    public function __toString(): string
    {
        return $this->pre->__toString();
    }
}