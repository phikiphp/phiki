<?php

namespace Phiki\Phast;

use Stringable;

class ClassList implements Stringable
{
    public function __construct(
        public array $classes = [],
    ) {}

    public function contains(string $class): bool
    {
        return in_array($class, $this->classes, true);
    }

    public function toggle(string $class, bool $state = true): self
    {
        if ($state && ! $this->contains($class)) {
            $this->add($class);
        } elseif (! $state && $this->contains($class)) {
            $this->remove($class);
        }

        return $this;
    }

    public function add(string ...$class): self
    {
        $this->classes = array_unique(array_merge($this->classes, $class));

        return $this;
    }

    public function remove(string ...$class): self
    {
        $this->classes = array_filter($this->classes, fn (string $c) => ! in_array($c, $class, true));

        return $this;
    }

    public function all(): array
    {
        return $this->classes;
    }

    public function isEmpty(): bool
    {
        return empty($this->classes);
    }

    public function __toString(): string
    {
        return implode(' ', array_filter($this->classes, fn (string $class) => trim($class) !== ''));
    }
}
