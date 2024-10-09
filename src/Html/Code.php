<?php

namespace Phiki\Html;

class Code extends Element
{
    public function __construct(
        public AttributeList $attributes = new AttributeList,
        public array $children = [],
    ) {
        parent::__construct('code', $attributes, $children);
    }
}
