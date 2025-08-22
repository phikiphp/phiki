<?php

namespace Phiki\Decorations;

use Closure;
use Phiki\Transformers\AbstractTransformer;

class DecorationTransformer extends AbstractTransformer
{
    /**
     * Create a new instance.
     * 
     * @param Closure(): array<Decoration> $producer
     */
    public function __construct(private Closure $producer) {}
}
