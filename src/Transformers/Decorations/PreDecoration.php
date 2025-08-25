<?php

namespace Phiki\Transformers\Decorations;

use Phiki\Phast\ClassList;

class PreDecoration
{
    public function __construct(
        public ClassList $classes,
    ) {}

    public static function make(): self
    {
        return new self(new ClassList());
    }

    public function class(string ...$classes): self
    {
        $this->classes->add(...$classes);
        
        return $this;
    }
}
