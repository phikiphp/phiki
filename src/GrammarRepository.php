<?php

namespace Phiki;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;

class GrammarRepository implements GrammarRepositoryInterface
{
    protected array $grammars = [
        'php' => __DIR__ . '/../languages/php.json',
    ];
    
    public function get(string $name): array
    {
        if (! $this->has($name)) {
            throw UnrecognisedGrammarException::make($name);
        } 

        $grammar = $this->grammars[$name];

        if (is_array($grammar)) {
            return $grammar;
        }

        return $this->grammars[$name] = json_decode(file_get_contents($grammar), true);
    }

    public function has(string $name): bool
    {
        return isset($this->grammars[$name]);
    }

    public function register(string $name, string|array $pathOrGrammar): void
    {
        $this->grammars[$name] = $pathOrGrammar;
    }
}