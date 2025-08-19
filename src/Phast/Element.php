<?php

namespace Phiki\Phast;

use Stringable;

class Element implements Stringable
{
    /**
     * @param array<Element | Text> $children
     */
    public function __construct(
        public string $tagName,
        public Properties $properties = new Properties(),
        public array $children = [],
    ) {}

    public function __toString(): string
    {
        $element = sprintf(
            '<%s%s>',
            $this->tagName,
            count($this->properties->properties) > 0 ? ' ' . (string) $this->properties : ''
        );

        foreach ($this->children as $child) {
            $element .= (string) $child;
        }

        $element .= sprintf('</%s>', $this->tagName);

        return $element;
    }
}
