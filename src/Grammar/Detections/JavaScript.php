<?php

namespace Phiki\Grammar\Detections;

use Phiki\Contracts\GrammarDetectionInterface;
use Phiki\Grammar\Grammar;

class JavaScript implements GrammarDetectionInterface
{
    public function getPatterns(): array
    {
        return [
            '/undefined/',
            '/console\.log\s*/',
            '/window\./',
            '/\(.*\) => {/',
        ];
    }

    public function getGrammar(): Grammar|string
    {
        return Grammar::Javascript;
    }
}
