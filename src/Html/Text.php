<?php

namespace Phiki\Html;

use Stringable;

class Text implements Stringable
{
    public function __construct(
        public string $value,
    ) {}

    public function __toString(): string
    {
        return htmlspecialchars($this->value);
    }
}