<?php

namespace Phiki\Phast;

use Stringable;

class Element implements Stringable
{
    /**
     * @param  array<Element | Text>  $children
     */
    public function __construct(
        public string $tagName,
        public Properties $properties = new Properties,
        public array $children = [],
    ) {}

    public function __toString(): string
    {
        $properties = (string) $this->properties;

        $element = sprintf(
            '<%s%s>',
            $this->tagName,
            $properties ? ' '.$properties : ''
        );

        foreach ($this->children as $child) {
            $element .= (string) $child;
        }

        $element .= sprintf('</%s>', $this->tagName);

        return $element;
    }
}
