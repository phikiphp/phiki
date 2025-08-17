<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;

enum Grammar: string
{
    {cases}

    public function aliases(): array
    {
        return match ($this) {
            {aliases}
        };
    }

    public function scopeName(): string
    {
        return match ($this) {
            {scopeNames}
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
