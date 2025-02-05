<?php

namespace Phiki\Contracts;

use Phiki\Grammar\Grammar;

interface GrammarDetectionInterface
{
    /**
     * Return an array of patterns that should be used during language auto-detection.
     *
     * It's important that these patterns are unique to the language and not shared with other languages.
     *
     * @return string[]
     */
    public function getPatterns(): array;

    /**
     * Return the grammar that should be used when the language is detected.
     */
    public function getGrammar(): Grammar|string;
}
