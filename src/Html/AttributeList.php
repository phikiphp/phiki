<?php

namespace Phiki\Html;

use Stringable;

class AttributeList implements Stringable
{
    public function __construct(
        public array $attributes = [],
    ) {}

    public function addClass(string $class): void
    {
        $classes = explode(' ', $this->attributes['class'] ?? '');
        $classes[] = $class;

        $this->set('class', implode(' ', array_unique($classes)));
    }

    public function removeClass(string $class): void
    {
        $classes = explode(' ', $this->attributes['class'] ?? '');
        $classes = array_filter($classes, fn (string $c) => $c !== $class);

        $this->set('class', implode(' ', array_unique($classes)));
    }

    public function set(string $attribute, ?string $value = null): void
    {
        $this->attributes[$attribute] = $value;
    }

    public function has(string $attribute): bool
    {
        return isset($this->attributes[$attribute]);
    }

    public function get(string $attribute): ?string
    {
        return $this->attributes[$attribute] ?? null;
    }

    public function remove(string $attribute): void
    {
        if (!isset($this->attributes[$attribute])) {
            return;
        }

        unset($this->attributes[$attribute]);
    }

    public function __toString(): string
    {
        return implode(
            ' ',
            array_map(
                fn (string $attribute, ?string $value) => $value === null
                    ? $attribute
                    : sprintf('%s="%s"', $attribute, htmlspecialchars($value)),
                array_keys($this->attributes),
                $this->attributes,
            ),
        );
    }
}