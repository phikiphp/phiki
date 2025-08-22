<?php

namespace Phiki\Decorations;

use Phiki\Phast\Properties;

class Decoration
{
    public function __construct(
        public Properties $properties,
        public DecorationLocation $start,
        public ?DecorationLocation $end = null,
    ) {}
}
