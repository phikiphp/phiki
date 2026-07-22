<?php

namespace Phiki\Ansi;

use Phiki\Token\Token;

class AnsiToken extends Token
{
    public function __construct(
        string $text,
        int $start,
        int $end,
        public readonly ?string $foreground = null,
        public readonly ?string $background = null,
        public readonly ?string $fontStyle = null,
    ) {
        parent::__construct(['text.ansi'], $text, $start, $end);
    }
}
