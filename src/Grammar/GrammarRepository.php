<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;

class GrammarRepository implements GrammarRepositoryInterface
{
    protected array $grammars = DefaultGrammars::NAMES_TO_PATHS;

    protected array $scopesToGrammar = DefaultGrammars::SCOPES_TO_NAMES;

    protected array $aliases = [
        'bash' => 'shellscript',
        'sh' => 'shellscript',
        'shell' => 'shellscript',
        'js' => 'javascript',
        'yml' => 'yaml',
        'golang' => 'go',
        'text' => 'txt',
        'plaintext' => 'txt',
        'md' => 'markdown',
        'py' => 'python',
    ];

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

        $parser = new Parser;

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

    public function getAllGrammarNames(): array
    {
        return array_keys($this->grammars);
    }
}
