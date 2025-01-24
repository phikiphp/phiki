<?php

namespace Phiki\Languages\Detections;

use Phiki\Contracts\LanguageDetectionInterface;
use Phiki\Grammar\Grammar;

class JavaScript implements LanguageDetectionInterface
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
