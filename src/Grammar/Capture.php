<?php

namespace Phiki\Grammar;

class Capture
{
    public function __construct(
        public string $index,
        public ?string $name,
        public array $patterns = [],
    ) {}
}