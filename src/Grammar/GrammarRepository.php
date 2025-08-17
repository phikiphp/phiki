<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarDetectionInterface;
use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;
use Phiki\Grammar\Detections\JavaScript;
use Phiki\Grammar\Detections\Php;

class GrammarRepository implements GrammarRepositoryInterface
{
    protected array $grammars = [];

    protected array $scopesToGrammar = [];

    protected array $aliases = [];

    protected array $detections = [];

    public function __construct()
    {
        foreach (Grammar::cases() as $grammar) {
            $this->grammars[$grammar->value] = $grammar->path();
            $this->scopesToGrammar[$grammar->scopeName()] = $grammar->value;

            foreach ($grammar->aliases() as $alias) {
                $this->aliases[$alias] = $grammar->value;
            }
        }

        $this->detections = [
            new JavaScript,
            new Php,
        ];  
    }

    public function get(string $name): ParsedGrammar
    {
        if (! $this->has($name)) {
            throw UnrecognisedGrammarException::make($name);
        }

        $name = $this->aliases[$name] ?? $name;
        $grammar = $this->grammars[$name];

        if ($grammar instanceof ParsedGrammar) {
            return $grammar;
        }

        $parser = new GrammarParser;

        return $this->grammars[$name] = $parser->parse(json_decode(file_get_contents($grammar), true));
    }

    public function getFromScope(string $scope): ParsedGrammar
    {
        if (! isset($this->scopesToGrammar[$scope])) {
            throw UnrecognisedGrammarException::make($scope);
        }

        return $this->get($this->scopesToGrammar[$scope]);
    }

    public function has(string $name): bool
    {
        return isset($this->grammars[$name]) || isset($this->aliases[$name]);
    }

    public function alias(string $alias, string $target): void
    {
        $this->aliases[$alias] = $target;
    }

    public function register(string $name, string|ParsedGrammar $pathOrGrammar): void
    {
        $this->grammars[$name] = $pathOrGrammar;
    }

    public function addDetection(GrammarDetectionInterface $detection): void
    {
        $this->detections[] = $detection;
    }

    public function detections(): array
    {
        return $this->detections;
    }
}
