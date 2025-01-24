<?php

namespace Phiki\Languages\Detections;

use Phiki\Contracts\LanguageDetectionInterface;
use Phiki\Grammar\Grammar;

class Php implements LanguageDetectionInterface
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
