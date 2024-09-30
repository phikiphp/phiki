<?php

namespace Phiki\Grammar\Injections;

use Stringable;

class Scope implements Stringable
{
    public function __construct(
        public array $parts,
    ) {}

    public function __toString(): string
    {
        return implode('.', $this->parts);
    }
}