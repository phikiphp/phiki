<?php

namespace Phiki\Html;

class Pre extends Element
{
    public function __construct(
        public Code $code,
        public AttributeList $attributes = new AttributeList,
    ) {
        parent::__construct('pre', $attributes, [$code]);
    }
}
