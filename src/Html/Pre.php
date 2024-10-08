<?php

namespace Phiki\Html;

class Pre extends Element
{
    public function __construct(
        public AttributeList $attributes = new AttributeList(),
        public array $children = [],
    ) {
        parent::__construct('pre', $attributes, $children);
    }
}