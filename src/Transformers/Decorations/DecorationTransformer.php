<?php

namespace Phiki\Transformers\Decorations;

use Phiki\Phast\Element;
use Phiki\Transformers\AbstractTransformer;

class DecorationTransformer extends AbstractTransformer
{
    /**
     * @param array<int, LineDecoration> $decorations
     */
    public function __construct(
        public array &$decorations,
    ) {}

    public function line(Element $span, array $tokens, int $index): Element
    {
        foreach ($this->decorations as $decoration) {
            if (! $decoration->appliesToLine($index)) {
                continue;
            }

            $span->properties->get('class')->add(...$decoration->classes->all());
        }

        return $span;
    }
}
