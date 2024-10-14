<?php

namespace Phiki\Html;

class Root extends Element
{
    public function __construct(
        public Pre $pre,
        AttributeList $attributes = new AttributeList(),
    ) {
        parent::__construct('div', $attributes, [$pre]);
    }
}
