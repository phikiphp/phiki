<?php

namespace Phiki\Phast;

use Stringable;

class Properties implements Stringable
{
    /**
     * @param array<string, string | \Stringable> $properties
     */
    public function __construct(
        public array $properties = [],
    ) {}

    public function set(string $key, string | Stringable $value): self
    {
        $this->properties[$key] = $value;

        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->properties[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->properties);
    }

    public function remove(string $key): self
    {
        unset($this->properties[$key]);

        return $this;
    }

    public function __toString(): string
    {
        $properties = array_filter($this->properties, fn ($value) => !! $value);

        return implode(' ', array_map(
            fn ($key, $value) => sprintf('%s="%s"', $key, $value),
            array_keys($properties),
            $properties,
        ));
    }
}
