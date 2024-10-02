<?php

namespace Phiki\Contracts;

/**
 * @internal
 */
interface InjectionSelectorParserInputInterface
{
    public function current(): ?string;

    public function next(): void;

    public function peek(): ?string;
}
