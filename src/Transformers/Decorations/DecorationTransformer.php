<?php

namespace Phiki\Transformers\Decorations;

use Phiki\Phast\Element;
use Phiki\Transformers\AbstractTransformer;

class DecorationTransformer extends AbstractTransformer
{
    /**
     * @param  array<int, LineDecoration | PreDecoration | CodeDecoration | GutterDecoration>  $decorations
     */
    public function __construct(
        public array &$decorations,
    ) {}

    public function pre(Element $pre): Element
    {
        foreach ($this->decorations as $decoration) {
            if (! $decoration instanceof PreDecoration) {
                continue;
            }

            $pre->properties->get('class')->add(...$decoration->classes->all());
        }

        return $pre;
    }

    public function code(Element $code): Element
    {
        foreach ($this->decorations as $decoration) {
            if (! $decoration instanceof CodeDecoration) {
                continue;
            }

            $code->properties->get('class')->add(...$decoration->classes->all());
        }

        return $code;
    }

    public function line(Element $span, array $tokens, int $index): Element
    {
        foreach ($this->decorations as $decoration) {
            if (! $decoration instanceof LineDecoration) {
                continue;
            }

            if (! $decoration->appliesToLine($index)) {
                continue;
            }

            $span->properties->get('class')->add(...$decoration->classes->all());
        }

        return $span;
    }

    public function gutter(Element $span, int $lineNumber): Element
    {
        foreach ($this->decorations as $decoration) {
            if (! $decoration instanceof GutterDecoration) {
                continue;
            }

            $span->properties->get('class')->add(...$decoration->classes->all());
        }

        return $span;
    }
}
