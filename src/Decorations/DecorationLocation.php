<?php

namespace Phiki\Decorations;

class DecorationLocation
{
    public function __construct(
        public int $line,
        public ?int $character = null,
    ) {}
}
