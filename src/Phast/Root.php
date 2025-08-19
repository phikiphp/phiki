<?php

namespace Phiki\Phast;

use Stringable;

class Root implements Stringable
{
    /**
     * @param array<Element | Text> $children
     */
    public function __construct(
        public array $children = [],
    ) {}

    public function __toString(): string
    {
        return implode('', array_map(fn (Element | Text $child) => (string) $child, $this->children));
    }
}
