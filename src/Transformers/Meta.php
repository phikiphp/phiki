<?php

namespace Phiki\Transformers;

readonly class Meta
{
    public function __construct(
        public ?string $markdownInfo = null,
    ) {}
}
