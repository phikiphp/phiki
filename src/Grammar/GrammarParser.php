<?php

namespace Phiki\Grammar;

use Phiki\Contracts\InjectionSelectorParserInputInterface;
use Phiki\Contracts\PatternInterface;
use Phiki\Exceptions\MissingRequiredGrammarKeyException;
use Phiki\Grammar\Injections\Composite;
use Phiki\Grammar\Injections\Expression;
use Phiki\Grammar\Injections\Filter;
use Phiki\Grammar\Injections\Group;
use Phiki\Grammar\Injections\Injection;
use Phiki\Grammar\Injections\Operator;
use Phiki\Grammar\Injections\Path;
use Phiki\Grammar\Injections\Prefix;
use Phiki\Grammar\Injections\Scope;
use Phiki\Grammar\Injections\Selector;
use Phiki\Support\Regex;

class GrammarParser
{
    protected string $scopeName;

    protected bool $injection = false;

    protected int $id = 0;

    /**
     * Repository keys for the names that `#name` includes refer to in the rule being parsed, for names
     * defined by the repository of an enclosing rule rather than the grammar's own repository.
     *
     * @var array<string, string>
     */
    protected array $references = [];

    /**
     * Rules from the repositories of rules, keyed so they can't clash with the grammar's own repository.
     *
     * @var array<string, PatternInterface>
     */
    protected array $nestedRepository = [];

    public function parse(array $grammar): ParsedGrammar
    {
        if (! isset($grammar['scopeName'])) {
            throw MissingRequiredGrammarKeyException::make('scopeName');
        }

        $this->scopeName = $scopeName = $grammar['scopeName'];
        $this->references = [];
        $this->nestedRepository = [];

        $patterns = $this->patterns($grammar['patterns'] ?? []);
        $repository = [];

        foreach ($grammar['repository'] ?? [] as $name => $pattern) {
            if ($pattern = $this->pattern($pattern)) {
                $repository[$name] = $pattern;
            }
        }

        $injections = [];

        foreach ($grammar['injections'] ?? [] as $selector => $injection) {
            $injections[] = $this->injection($selector, $injection);
        }

        $name = $grammar['name'] ?? null;

        return new ParsedGrammar($name, $scopeName, $patterns, $repository + $this->nestedRepository, $injections);
    }

    protected function pattern(array $pattern): PatternInterface|false
    {
        // vscode-textmate treats an empty name the same as no name.
        $pattern = array_filter($pattern, fn ($value, $key) => ! in_array($key, ['name', 'contentName'], true) || ($value !== '' && $value !== null), ARRAY_FILTER_USE_BOTH);

        // The checks below follow how vscode-textmate decides what kind of rule a pattern is.
        if (isset($pattern['match']) && $pattern['match'] !== '') {
            return new MatchPattern(
                $this->id(),
                new Regex($pattern['match']),
                $pattern['name'] ?? null,
                $this->captures($pattern['captures'] ?? []),
                injection: $this->injection,
            );
        }

        // A rule without `begin` can have its own repository. `#name` includes inside the rule, including
        // inside that repository, resolve to its rules before the enclosing ones.
        if (! isset($pattern['begin']) && isset($pattern['repository']) && is_array($pattern['repository'])) {
            $references = $this->references;

            foreach (array_keys($pattern['repository']) as $name) {
                $this->references[$name] = $name."\0".$this->id();
            }

            try {
                foreach ($pattern['repository'] as $name => $rule) {
                    if (is_array($rule) && ($parsed = $this->pattern($rule))) {
                        $this->nestedRepository[$this->references[$name]] = $parsed;
                    }
                }

                unset($pattern['repository']);

                return $this->pattern($pattern);
            } finally {
                $this->references = $references;
            }
        }

        // An empty begin matches straight away, but mbregex can't search for an empty pattern.
        if (($pattern['begin'] ?? null) === '') {
            $pattern['begin'] = '(?:)';
        }

        if (isset($pattern['begin']) && ($pattern['while'] ?? '') !== '') {
            return new BeginWhilePattern(
                $this->id(),
                new Regex($pattern['begin']),
                new Regex($pattern['while']),
                $pattern['name'] ?? null,
                $pattern['contentName'] ?? null,
                $this->captures($pattern['beginCaptures'] ?? []),
                $this->captures($pattern['whileCaptures'] ?? []),
                $this->captures($pattern['captures'] ?? []),
                $this->patterns($pattern['patterns'] ?? []),
                injection: $this->injection,
            );
        }

        if (isset($pattern['begin'])) {
            return new BeginEndPattern(
                $this->id(),
                new Regex($pattern['begin']),
                // A missing or empty end never matches, so the rule continues to the end of the code.
                new Regex(($pattern['end'] ?? '') === '' ? '(?!)' : $pattern['end']),
                $pattern['name'] ?? null,
                $pattern['contentName'] ?? null,
                $this->captures($pattern['beginCaptures'] ?? []),
                $this->captures($pattern['endCaptures'] ?? []),
                $this->captures($pattern['captures'] ?? []),
                $this->patterns($pattern['patterns'] ?? []),
                injection: $this->injection,
                applyEndPatternLast: (bool) ($pattern['applyEndPatternLast'] ?? false),
            );
        }

        if (isset($pattern['patterns'])) {
            return new CollectionPattern($this->id(), $this->patterns($pattern['patterns']), injection: $this->injection);
        }

        if (isset($pattern['include'])) {
            if (str_starts_with($pattern['include'], '#')) {
                $reference = substr($pattern['include'], 1);
                [$reference, $scopeName] = [$this->references[$reference] ?? $reference, $this->scopeName];
            } elseif ($pattern['include'] === '$self') {
                [$reference, $scopeName] = ['$self', $this->scopeName];
            } elseif ($pattern['include'] === '$base') {
                [$reference, $scopeName] = ['$base', null];
            } elseif (str_contains($pattern['include'], '#')) {
                [$scopeName, $reference] = explode('#', $pattern['include']);
            } else {
                [$reference, $scopeName] = [null, $pattern['include']];
            }

            return new IncludePattern($this->id(), $reference, $scopeName, injection: $this->injection);
        }

        if (array_is_list($pattern)) {
            return new CollectionPattern($this->id(), $this->patterns($pattern), injection: $this->injection);
        }

        return false;
    }

    protected function patterns(array $patterns): array
    {
        $result = [];

        foreach ($patterns as $pattern) {
            if ($pattern = $this->pattern($pattern)) {
                $result[] = $pattern;
            }
        }

        return $result;
    }

    protected function captures(array $captures): array
    {
        $result = [];

        foreach ($captures as $index => $capture) {
            if ($capture === null) {
                continue;
            }

            $result[$index] = $this->capture($capture, strval($index));
        }

        $captureIndices = array_filter(array_keys($result), fn ($index) => is_numeric($index));
        $maxCaptureIdx = $captureIndices === [] ? 0 : max($captureIndices);

        for ($i = 0; $i <= $maxCaptureIdx; $i++) {
            if (! isset($result[$i])) {
                $result[$i] = null;
            }
        }

        return $result;
    }

    protected function capture(array|string $capture, string $index): Capture
    {
        if (is_string($capture)) {
            return new Capture($this->id(), $index, $capture === '' ? null : $capture, new CollectionPattern($this->id(), [], $this->injection));
        }

        return new Capture($this->id(), $index, ($capture['name'] ?? '') === '' ? null : $capture['name'], new CollectionPattern($this->id(), $this->patterns($capture['patterns'] ?? []), $this->injection));
    }

    protected function injection(string $selector, array $injection): Injection
    {
        $input = new class($selector) implements InjectionSelectorParserInputInterface
        {
            /**
             * @var list<string>
             */
            private array $tokens;

            private int $offset = 0;

            public function __construct(string $selector)
            {
                // Split the selector into tokens the same way vscode-textmate does: prefixes, scope names
                // (which can contain `-`), and operators. A `-` only negates when it's not inside a name.
                preg_match_all('/[LR]:|[\w.:][\w.:\-]*|[,|&\-()]/', $selector, $matches);

                $this->tokens = $matches[0];
            }

            public function current(): ?string
            {
                return $this->tokens[$this->offset] ?? null;
            }

            public function peek(): ?string
            {
                return $this->tokens[$this->offset + 1] ?? null;
            }

            public function next(): void
            {
                $this->offset++;
            }
        };

        $this->injection = true;

        $selector = $this->selector($input);
        $pattern = $this->pattern($injection);

        $this->injection = false;

        return new Injection($this->id(), $selector, $pattern);
    }

    protected function selector(InjectionSelectorParserInputInterface $input): Selector
    {
        $composites = [$this->composite($input)];

        while ($input->current() === ',') {
            $input->next();
            $composites[] = $this->composite($input);
        }

        return new Selector($composites);
    }

    /**
     * Parse operands joined by `|`. Operands next to each other, or joined by `&` or `-`, bind more tightly
     * than `|`, so `a | b - c` means `a | (b - c)`, as in vscode-textmate.
     */
    protected function composite(InjectionSelectorParserInputInterface $input): Composite
    {
        $conjunctions = [$this->conjunction($input)];

        while ($input->current() === '|') {
            $input->next();
            $conjunctions[] = $this->conjunction($input);
        }

        if (count($conjunctions) === 1) {
            return new Composite($conjunctions[0]);
        }

        return new Composite(array_map(function (array $expressions, int $index) {
            $operator = $index === 0 ? Operator::None : Operator::Or;

            if (count($expressions) === 1) {
                return new Expression($expressions[0]->child, $operator, $expressions[0]->negated);
            }

            return new Expression(new Group(new Selector([new Composite($expressions)])), $operator);
        }, $conjunctions, array_keys($conjunctions)));
    }

    /**
     * @return list<Expression>
     */
    protected function conjunction(InjectionSelectorParserInputInterface $input): array
    {
        $expressions = [];

        while (true) {
            $operator = $expressions === [] ? Operator::None : Operator::And;
            $negated = false;

            if ($input->current() === '&') {
                $input->next();
            } elseif ($input->current() === '-') {
                $input->next();
                $expressions === [] ? $negated = true : $operator = Operator::Not;
            }

            $child = $this->operand($input);

            if ($child === null) {
                return $expressions;
            }

            $expressions[] = new Expression($child, $operator, $negated);
        }
    }

    protected function operand(InjectionSelectorParserInputInterface $input): Filter|Group|Path|null
    {
        $current = $input->current();

        if ($current === 'L:' || $current === 'R:') {
            $input->next();
            $child = $this->operand($input);

            return $child instanceof Filter || $child === null ? $child : new Filter($child, $current === 'L:' ? Prefix::Left : Prefix::Right);
        }

        if ($current === '(') {
            $input->next();
            $child = $this->selector($input);

            if ($input->current() === ')') {
                $input->next();
            }

            return new Group($child);
        }

        $scopes = [];

        while (($current = $input->current()) !== null && preg_match('/^[\w.:]/', $current)) {
            $scopes[] = Scope::fromString($current);
            $input->next();
        }

        return $scopes === [] ? null : new Path($scopes);
    }

    protected function id(): int
    {
        return ++$this->id;
    }
}
