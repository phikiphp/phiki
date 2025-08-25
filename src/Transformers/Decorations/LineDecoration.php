<?php

namespace Phiki\Transformers\Decorations;

use Phiki\Phast\ClassList;

class LineDecoration
{
    /**
     * @param int | array<int> $line
     */
    public function __construct(
        public int | array $line,
        public ClassList $classes,
    ) {}

    public function appliesToLine(int $line): bool
    {
        return $this->line === $line || (is_array($this->line) && $line >= $this->line[0] && $line <= $this->line[1]);
    }
}
