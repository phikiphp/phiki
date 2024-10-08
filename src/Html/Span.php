<?php

namespace Phiki\Html;

class Span extends Element
{
    public function __construct(
        public AttributeList $attributes = new AttributeList(),
        public array $children = [],
    ) {
        parent::__construct('span', $attributes, $children);
    }
}