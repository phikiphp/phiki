<?php

namespace Phiki;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;
use Phiki\Grammar\Grammar;

class GrammarRepository implements GrammarRepositoryInterface
{
    protected array $grammars = [
        'blade' => __DIR__ . '/../languages/blade.json',
        'php' => __DIR__ . '/../languages/php.json',
        'html' => __DIR__ . '/../languages/html.json',
    ];

    protected array $scopesToGrammar = [
        'text.html.basic' => 'html',
        'text.html.php.blade' => 'blade',
        'source.php' => 'php',
    ];
    
    public function get(string $name): Grammar
    {
        if (! $this->has($name)) {
            throw UnrecognisedGrammarException::make($name);
        } 

        $grammar = $this->grammars[$name];

        if ($grammar instanceof Grammar) {
            return $grammar;
        }

        $parser = new GrammarParser();

        return $this->grammars[$name] = $parser->parse(json_decode(file_get_contents($grammar), true));
    }

    public function getFromScope(string $scope): Grammar
    {
        if (! isset($this->scopesToGrammar[$scope])) {
            throw UnrecognisedGrammarException::make($scope);
        }

        return $this->get($this->scopesToGrammar[$scope]);
    }

    public function has(string $name): bool
    {
        return isset($this->grammars[$name]);
    }

    public function register(string $name, string|Grammar $pathOrGrammar): void
    {
        $this->grammars[$name] = $pathOrGrammar;
    }
}