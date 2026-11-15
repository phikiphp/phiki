<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;
use Phiki\TextMate\Engines\Engine;
use WeakMap;

class GrammarRepository implements GrammarRepositoryInterface
{
    /**
     * @var array<string, Grammar|string|ParsedGrammar>
     */
    protected array $grammars = [];

    /**
     * Grammars parsed from files, keyed by engine for the bundled ones.
     *
     * @var array<string, ParsedGrammar>
     */
    protected array $parsed = [];

    /**
     * The regex engine whose bundled grammars are loaded.
     */
    protected Engine $engine = Engine::Oniguruma;

    protected array $scopesToGrammar = [];

    protected array $aliases = [];

    /**
     * The pattern lists the tokenizer has compiled for each grammar, keyed by rule and anchor flags, so
     * each is compiled once rather than for every tokenize() call. Rules are included through this
     * repository, so the lists are dropped whenever the grammars it resolves change.
     *
     * @internal
     *
     * @var WeakMap<ParsedGrammar, array<string, array{ 0: PatternInterface, 1: array<array{ 0: PatternInterface, 1: string }> }>>
     */
    public WeakMap $compiled;

    public function __construct()
    {
        $this->compiled = new WeakMap;

        foreach (Grammar::cases() as $grammar) {
            $this->grammars[$grammar->value] = $grammar;
            $this->scopesToGrammar[$grammar->scopeName()] = $grammar->value;

            foreach ($grammar->aliases() as $alias) {
                $this->aliases[$alias] = $grammar->value;
            }
        }
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

        // Bundled grammars are written for each engine. Registered files are used as they are.
        [$key, $path] = $grammar instanceof Grammar
            ? ["{$this->engine->value}:{$name}", $grammar->path($this->engine)]
            : [$name, $grammar];

        return $this->parsed[$key] ??= (new GrammarParser)->parse(json_decode(file_get_contents($path), true));
    }

    /**
     * Load the bundled grammars written for the given regex engine.
     */
    public function engine(Engine $engine): void
    {
        $this->engine = $engine;
        $this->compiled = new WeakMap;
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
        $this->compiled = new WeakMap;
    }

    public function register(string $name, string|ParsedGrammar $pathOrGrammar): void
    {
        $this->grammars[$name] = $pathOrGrammar;
        unset($this->parsed[$name]);
        $this->compiled = new WeakMap;
    }

    public function resolve(string|Grammar|ParsedGrammar $grammar): ParsedGrammar
    {
        if ($grammar instanceof ParsedGrammar) {
            return $grammar;
        }

        return match (true) {
            is_string($grammar) => $this->get($grammar),
            $grammar instanceof Grammar => $grammar->toParsedGrammar($this),
        };
    }
}
