<?php

namespace Phiki\Decorations;

use Closure;
use Phiki\Phast\Element;
use Phiki\Transformers\AbstractTransformer;

class DecorationTransformer extends AbstractTransformer
{
    /**
     * @var array<int, array<Decoration>>
     */
    private ?array $decorations = null;

    /**
     * Create a new instance.
     * 
     * @param Closure(): array<int, Decoration> $producer
     */
    public function __construct(private Closure $producer) {}

    /**
     * Modify the <span> for each line.
     *
     * @param  array<int, HighlightedToken>  $line
     */
    public function line(Element $span, array $tokens, int $index): Element
    {
        $this->ensureDecorationsCollected();

        return $span;
    }

    /**
     * Get the decorations.
     * 
     * @return array<int, Decoration>
     */
    private function ensureDecorationsCollected(): void
    {
        if ($this->decorations !== null) {
            return;
        }

        /** @var array<int, Decoration> $decorations */
        $decorations = $this->producer->__invoke();
        $mapped = [];

        foreach ($decorations as $decoration) {
            // This is a decoration that applies to the entire line.
            if ($decoration->end === null && $decoration->start->character === null) {
                $mapped[$decoration->start->line][] = $decoration;
            }
        }
    }
}
