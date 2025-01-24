<?php

namespace Phiki\Grammar\Detections;

use Phiki\Contracts\GrammarDetectionInterface;
use Phiki\Grammar\Grammar;

class Php implements GrammarDetectionInterface
{
    public function getPatterns(): array
    {
        return [
            '/<\?php/',
            '/<\?=/',
            '/\?>/',
            '/(require|include)_once',
        ];
    }

    public function getGrammar(): Grammar|string
    {
        return Grammar::Php;
    }
}
