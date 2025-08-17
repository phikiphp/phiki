<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;

enum Grammar: string
{
    case Txt = 'txt';
    case Antlers = 'antlers';
    {cases}

    public function aliases(): array
    {
        return match ($this) {
            {aliases}
            self::Antlers => [],
            self::Txt => [],
        };
    }

    public function scopeName(): string
    {
        return match ($this) {
            {scopeNames}
            self::Antlers => 'text.html.statamic',
            self::Txt => 'text.plain'
        };
    }

    public function path(): string
    {
        return __DIR__ . "/../../resources/grammars/{$this->value}.json";
    }

    public static function parse(array $grammar): ParsedGrammar
    {
        return (new GrammarParser)->parse($grammar);
    }

    public function toParsedGrammar(GrammarRepositoryInterface $repository): ParsedGrammar
    {
        return $repository->get($this->value);
    }
}
