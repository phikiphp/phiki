<?php

namespace Phiki\Phast;

use Stringable;

class Literal implements Stringable
{
    public function __construct(
        public string $value,
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }
}
