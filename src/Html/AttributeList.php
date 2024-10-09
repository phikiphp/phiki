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

    public function setStyle(string $property, string $value): void
    {
        $styles = array_map(trim(...), explode(';', $this->attributes['style'] ?? ''));
        $styles[] = sprintf('%s: %s', $property, $value);

        $this->set('style', implode(';', array_unique($styles)));
    }

    public function removeStyle(string $property): void
    {
        $styles = array_map(trim(...), explode(';', $this->attributes['style'] ?? ''));
        $styles = array_filter($styles, fn (string $s) => !str_starts_with($s, $property . ':'));

        $this->set('style', implode(';', array_unique($styles)));
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