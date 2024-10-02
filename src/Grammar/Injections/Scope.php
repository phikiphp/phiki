<?php

namespace Phiki\Grammar\Injections;

use Stringable;

class Scope implements Stringable
{
    public function __construct(
        public array $parts,
    ) {}

    public function matches(Scope $scope): bool
    {
        foreach ($this->parts as $i => $part) {
            if ($part === '*') {
                continue;
            }

            if ($part !== ($scope->parts[$i] ?? null)) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        return implode('.', $this->parts);
    }

    public static function fromString(string $scope): self
    {
        return new self(explode('.', $scope));
    }
}
