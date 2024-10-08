<?php

namespace Phiki\Html;

use Stringable;

class Element implements Stringable
{
    /**
     * @param string $tagName
     * @param AttributeList $attributes
     * @param array<int, Element|Text> $children
     */
    public function __construct(
        public string $tagName,
        public AttributeList $attributes = new AttributeList(),
        public array $children = [],
    ) {}

    public function addChild(Element|Text $child): void
    {
        $this->children[] = $child;
    }

    public function __toString(): string
    {
        return sprintf(
            '<%s %s>%s</%s>',
            $this->tagName,
            $this->attributes,
            implode('', $this->children),
            $this->tagName,
        );
    }
}