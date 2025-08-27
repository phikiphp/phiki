<?php

namespace Phiki\Adapters\CommonMark\Transformers\Annotations;

use Phiki\Phast\Element;

class Annotation
{
    public function __construct(
        public AnnotationType $type,
        public int $start,
        public int $end,
    ) {}

    public function applyToLine(Element $line): void
    {
        $line->properties->get('class')->add(...$this->type->getLineClasses());
    }

    public function applyToPre(Element $pre): void
    {
        $pre->properties->get('class')->add(...$this->type->getPreClasses());
    }
}
