<?php

namespace Phiki\Grammar;

use Phiki\Contracts\InjectionSelectorParserInputInterface;
use Phiki\Exceptions\MissingRequiredGrammarKeyException;
use Phiki\Exceptions\UnreachableException;
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

class Parser
{
    protected string $scopeName;

    protected bool $injection = false;

    public function parse(array $grammar): ParsedGrammar
    {
        if (! isset($grammar['scopeName'])) {
            throw MissingRequiredGrammarKeyException::make('scopeName');
        }

        $this->scopeName = $scopeName = $grammar['scopeName'];

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

        return new ParsedGrammar($name, $scopeName, $patterns, $repository, $injections);
    }

    protected function pattern(array $pattern): Pattern|false
    {
        if (isset($pattern['match'])) {
            return new MatchPattern(
                new Regex($pattern['match']),
                $pattern['name'] ?? null,
                $this->captures($pattern['captures'] ?? []),
                injection: $this->injection,
            );
        }

        if (isset($pattern['begin'], $pattern['end'])) {
            return new BeginEndPattern(
                new Regex($pattern['begin']),
                new Regex($pattern['end']),
                $pattern['name'] ?? null,
                $pattern['contentName'] ?? null,
                $this->captures($pattern['beginCaptures'] ?? []),
                $this->captures($pattern['endCaptures'] ?? []),
                $this->captures($pattern['captures'] ?? []),
                $this->patterns($pattern['patterns'] ?? []),
                injection: $this->injection,
            );
        }

        if (isset($pattern['begin'], $pattern['while'])) {
            return new BeginWhilePattern(
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

        // This is more of a special case because it shouldn't ever happen, but we want
        // to be graceful and treat a standalone begin as a match.
        if (isset($pattern['begin']) && ! isset($pattern['end']) && ! isset($pattern['while'])) {
            return new MatchPattern(
                new Regex($pattern['begin']),
                $pattern['name'] ?? null,
                $this->captures($pattern['beginCaptures'] ?? $pattern['captures'] ?? []),
                injection: $this->injection,
            );
        }

        if (isset($pattern['include'])) {
            if (str_starts_with($pattern['include'], '#')) {
                [$reference, $scopeName] = [substr($pattern['include'], 1), $this->scopeName];
            } elseif ($pattern['include'] === '$self') {
                [$reference, $scopeName] = ['$self', $this->scopeName];
            } elseif ($pattern['include'] === '$base') {
                [$reference, $scopeName] = ['$base', null];
            } elseif (str_contains($pattern['include'], '#')) {
                [$scopeName, $reference] = explode('#', $pattern['include']);
            } else {
                [$reference, $scopeName] = [null, $pattern['include']];
            }

            return new IncludePattern($reference, $scopeName, injection: $this->injection);
        }

        if (isset($pattern['patterns'])) {
            return new CollectionPattern($this->patterns($pattern['patterns']), injection: $this->injection);
        }

        if (array_is_list($pattern)) {
            return new CollectionPattern($this->patterns($pattern), injection: $this->injection);
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
            $result[$index] = $this->capture($capture, $index);
        }

        return $result;
    }

    protected function capture(array|string $capture, string $index): Capture
    {
        if (is_string($capture)) {
            return new Capture($index, $capture, []);
        }

        return new Capture($index, $capture['name'] ?? null, $this->patterns($capture['patterns'] ?? []));
    }

    protected function injection(string $selector, array $injection): Injection
    {
        $input = new class($selector) implements InjectionSelectorParserInputInterface
        {
            private string $selector;

            private int $offset = 0;

            public function __construct(string $selector)
            {
                // Remove whitespace from the selector so we don't need to skip it in the parser.
                // Only exception is when the whitespace is between two letters, because it means
                // it's part of a path / scope pattern.
                $this->selector = preg_replace('/(?<![a-zA-Z])\s+|\s+(?![a-zA-Z])/', '', $selector);
            }

            public function current(): ?string
            {
                return $this->selector[$this->offset] ?? null;
            }

            public function peek(): ?string
            {
                return $this->selector[$this->offset + 1] ?? null;
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

        return new Injection($selector, $pattern);
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

    protected function composite(InjectionSelectorParserInputInterface $input): Composite
    {
        $expressions = [$this->expression($input)];

        while ($input->current() !== null && in_array($input->current(), ['&', '|', '-'])) {
            $operator = match ($input->current()) {
                '&' => Operator::And,
                '|' => Operator::Or,
                '-' => Operator::Not,
            };

            $input->next();

            $expressions[] = $this->expression($input, $operator);
        }

        return new Composite($expressions);
    }

    protected function expression(InjectionSelectorParserInputInterface $input, Operator $operator = Operator::None): Expression
    {
        $negated = false;

        if ($input->current() === '-') {
            $negated = true;
            $input->next();
        }

        if (in_array($input->current(), ['L', 'R']) && $input->peek() === ':') {
            $child = $this->filter($input);
        } elseif ($input->current() === '(') {
            $child = $this->group($input);
        } else {
            $child = $this->path($input);
        }

        return new Expression($child, $operator, $negated);
    }

    protected function filter(InjectionSelectorParserInputInterface $input): Filter
    {
        $prefix = match ($input->current()) {
            'L' => Prefix::Left,
            'R' => Prefix::Right,
            default => throw new UnreachableException('Unrecognised prefix in filter: '.$input->current()),
        };

        $input->next();
        $input->next();

        if ($input->current() === '(') {
            $child = $this->group($input);
        } else {
            $child = $this->path($input);
        }

        return new Filter($child, $prefix);
    }

    protected function group(InjectionSelectorParserInputInterface $input): Group
    {
        if ($input->current() !== '(') {
            throw new UnreachableException('Expected "(" in group.');
        }

        $input->next();

        $child = $this->selector($input);

        if ($input->current() !== ')') {
            throw new UnreachableException('Expected ")" in group.');
        }

        $input->next();

        return new Group($child);
    }

    protected function path(InjectionSelectorParserInputInterface $input): Path
    {
        $scopes = [$this->scope($input)];

        while ($input->current() === ' ') {
            while ($input->current() === ' ') {
                $input->next();
            }

            $scopes[] = $this->scope($input);
        }

        return new Path($scopes);
    }

    protected function scope(InjectionSelectorParserInputInterface $input): Scope
    {
        $parts = [];

        do {
            if ($input->current() === '.') {
                $input->next();
            }

            $part = '';

            while ($input->current() !== null && (ctype_alpha($input->current()) || $input->current() === '*')) {
                $part .= $input->current();
                $input->next();
            }

            $parts[] = $part;
        } while ($input->current() === '.');

        return new Scope($parts);
    }
}
