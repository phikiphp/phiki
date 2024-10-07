<?php

namespace Phiki\Contracts;

interface OutputGeneratorInterface
{
    /**
     * Take the list of highlighted tokens and produce the final output.
     *
     * @param  list<list<\Phiki\Token\HighlightedToken>>  $tokens
     */
    public function generate(array $tokens): string;
}
