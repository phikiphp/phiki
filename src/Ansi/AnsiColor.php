<?php

namespace Phiki\Ansi;

abstract class AnsiColor
{
    abstract public function resolve(AnsiPalette $palette): string;
}
